<?php

namespace App\Livewire\Tickets;

use App\Models\SicodeUser;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use ZipArchive;

#[Layout('layouts.app')]
class Show extends Component
{
    public Ticket $ticket;

    /** @var array<string,array{id:string,name:string,email:?string}> */
    public array $participants = [];

    /** @var array<int,array{type:string,actor:?array{ id:string, name:string, email:?string}, meta:array, happened_at:string}> */
    public array $timeline = [];

    /** @var array<int,array{author:?array{id:string,name:string,email:?string}, body:string, created_at:string}> */
    public array $comments = [];

    /** @var array<int,array{id:int,filename:string,url:?string,size_label:?string,is_image:bool,mime:?string,uploader:?array{id:string,name:string,email:?string}, uploaded_at:string}> */
    public array $attachments = [];
    public array $imageAttachments = [];
    public array $fileAttachments = [];
    public array $selectedAttachments = [];

    public function mount(Ticket $ticket): void
    {
        $this->ensureViewerIsRequester($ticket);

        $this->ticket = $ticket->loadMissing([
            'area:id,name,sigla',
            'type:id,name',
            'category:id,name',
            'subcategory:id,name',
            'workflow:id,name',
            'step:id,name',
        ]);

        $this->participants = $this->resolveParticipants($ticket);
        $this->timeline = $this->buildTimeline($ticket);
        $this->comments = $this->buildComments($ticket);
        $attachments = $this->buildAttachments($ticket);
        $this->attachments = $attachments;
        $this->imageAttachments = array_values(array_filter($attachments, fn ($a) => $a['is_image']));
        $this->fileAttachments = array_values(array_filter($attachments, fn ($a) => ! $a['is_image']));
        $this->selectedAttachments = [];
    }

    public function render()
    {
        return view('livewire.tickets.show', [
            'ticket'       => $this->ticket,
            'participants' => $this->participants,
            'timeline'     => $this->timeline,
            'comments'     => $this->comments,
            'attachments'  => $this->attachments,
            'imageAttachments' => $this->imageAttachments,
            'fileAttachments' => $this->fileAttachments,
            'selectedAttachments' => $this->selectedAttachments,
        ]);
    }

    private function ensureViewerIsRequester(Ticket $ticket): void
    {
        if (Auth::id() !== $ticket->requester_sicode_id) {
            abort(403);
        }
    }

    private function resolveParticipants(Ticket $ticket): array
    {
        $roles = [
            'requester' => $ticket->requester_sicode_id,
            'executor'  => $ticket->executor_sicode_id,
            'manager'   => $ticket->manager_sicode_id,
        ];

        $ids = collect($roles)->filter()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $users = SicodeUser::query()
            ->select(['id', 'name', 'email'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($roles)
            ->map(function (?string $id, string $role) use ($users) {
                if (!$id) {
                    return null;
                }

                $user = $users->get($id);

                if (!$user) {
                    return null;
                }

                return [
                    'role'  => $role,
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                ];
            })
            ->filter()
            ->mapWithKeys(fn ($data) => [$data['role'] => $data])
            ->toArray();
    }

    private function buildTimeline(Ticket $ticket): array
    {
        return $ticket->events()
            ->with(['actor:id,name,email'])
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->map(function ($event) {
                $meta = collect($event->payload_json ?? [])
                    ->mapWithKeys(function ($value, $key) {
                        return [$this->metaLabel($key) => $value];
                    })
                    ->toArray();

                return [
                    'type'        => $event->type,
                    'label'       => $this->eventLabel($event->type),
                    'actor'       => $event->actor ? [
                        'id'    => $event->actor->id,
                        'name'  => $event->actor->name,
                        'email' => $event->actor->email,
                    ] : null,
                    'meta'        => $meta,
                    'happened_at' => $event->created_at?->locale('pt_BR')->diffForHumans(),
                ];
            })
            ->toArray();
    }

    private function buildComments(Ticket $ticket): array
    {
        return $ticket->comments()
            ->with(['author:id,name,email'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($comment) {
                return [
                    'author' => $comment->author ? [
                        'id'    => $comment->author->id,
                        'name'  => $comment->author->name,
                        'email' => $comment->author->email,
                    ] : null,
                    'body'       => $comment->body,
                    'created_at' => $comment->created_at?->locale('pt_BR')->diffForHumans(),
                ];
            })
            ->toArray();
    }

    private function buildAttachments(Ticket $ticket): array
    {
        return $ticket->attachments()
            ->with(['uploader:id,name,email'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($attachment) {
                $url = $this->attachmentUrl($attachment->path, $attachment->disk);
                $mime = $attachment->mime;
                $isImage = $mime ? Str::startsWith($mime, 'image/') : false;

                return [
                    'id'          => $attachment->id,
                    'filename'    => $attachment->filename,
                    'url'         => $url,
                    'size_label'  => $attachment->size_bytes ? $this->formatBytes((int) $attachment->size_bytes) : null,
                    'mime'        => $mime,
                    'is_image'    => $isImage,
                    'uploaded_at' => $attachment->created_at?->locale('pt_BR')->diffForHumans(),
                    'uploader'    => $attachment->uploader ? [
                        'id'    => $attachment->uploader->id,
                        'name'  => $attachment->uploader->name,
                        'email' => $attachment->uploader->email,
                    ] : null,
                ];
            })
            ->toArray();
    }

    private function attachmentUrl(?string $path, ?string $disk = null): ?string
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'ftp://'])) {
            return $path;
        }

        $diskName = $disk ?: config('filesystems.default');

        if (!Storage::disk($diskName)->exists($path)) {
            return null;
        }

        return Storage::disk($diskName)->url($path);
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return sprintf('%s %s', number_format($value, $power === 0 ? 0 : 1), $units[$power]);
    }

    private function eventLabel(?string $type): string
    {
        return match ($type) {
            'created' => 'Ticket criado',
            'attachment_added' => 'Anexo adicionado',
            'status_changed' => 'Status atualizado',
            'comment_added' => 'Comentário registrado',
            'assigned' => 'Ticket atribuído',
            'reassigned' => 'Ticket reatribuído',
            'closed' => 'Ticket encerrado',
            default => Str::of($type ?? 'evento')->replace('_', ' ')->headline()->toString(),
        };
    }

    private function metaLabel(string $key): string
    {
        return match ($key) {
            'code' => 'Código',
            'priority' => 'Prioridade',
            'area_id' => 'Área',
            'ticket_type_id' => 'Tipo de Ticket',
            'filename' => 'Arquivo',
            'original_name' => 'Nome original',
            'previous_status' => 'Status anterior',
            'new_status' => 'Novo status',
            'assigned_to' => 'Atribuído para',
            'assigned_by' => 'Atribuído por',
            default => Str::of($key)->replace('_', ' ')->headline()->toString(),
        };
    }

    public function downloadSelectedAttachments()
    {
        $ids = collect($this->selectedAttachments)
            ->filter(fn ($id) => !empty($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->count() < 2) {
            if ($ids->isEmpty()) {
                $this->dispatch('sweet-alert', [
                    'type' => 'error',
                    'title' => 'Nenhum anexo selecionado',
                    'text' => 'Escolha ao menos um arquivo para baixar.',
                    'toast' => true,
                ]);
                return null;
            }
        }

        $attachments = $this->ticket->attachments()
            ->whereIn('id', $ids)
            ->get();

        if ($attachments->isEmpty()) {
            $this->dispatch('sweet-alert', [
                'type' => 'error',
                'title' => 'Arquivos indisponíveis',
                'text' => 'Os anexos selecionados não foram encontrados.',
                'toast' => true,
            ]);
            return null;
        }

        if ($attachments->count() === 1) {
            $attachment = $attachments->first();

            if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
                $this->dispatch('sweet-alert', [
                    'type' => 'error',
                    'title' => 'Arquivo não encontrado',
                    'text' => 'O anexo selecionado não está mais disponível.',
                    'toast' => true,
                ]);
                return null;
            }

            $this->selectedAttachments = [];

            return Storage::disk($attachment->disk)->download(
                $attachment->path,
                $attachment->filename
            );
        }

        if (!class_exists(ZipArchive::class)) {
            $this->dispatch('sweet-alert', [
                'type' => 'error',
                'title' => 'Erro ao compactar',
                'text' => 'A extensão ZIP não está disponível no servidor.',
            ]);
            return null;
        }

        $this->selectedAttachments = [];

        $zipName = $this->ticket->code . '-anexos.zip';

        return response()->streamDownload(function () use ($attachments) {
            $zip = new ZipArchive();
            $tmp = tempnam(sys_get_temp_dir(), 'zip');

            if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Não foi possível gerar o arquivo ZIP.');
            }

            foreach ($attachments as $attachment) {
                if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
                    continue;
                }

                $stream = Storage::disk($attachment->disk)->readStream($attachment->path);

                if ($stream === false) {
                    continue;
                }

                $zip->addFromString($attachment->filename, stream_get_contents($stream));
                fclose($stream);
            }

            $zip->close();
            readfile($tmp);
            @unlink($tmp);
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function updatedSelectedAttachments($value): void
    {
        $this->selectedAttachments = collect($this->selectedAttachments)
            ->filter(fn ($value) => filled($value))
            ->values()
            ->toArray();
    }

    public function getCanDownloadSelectionProperty(): bool
    {
        return collect($this->selectedAttachments)
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->count() > 1;
    }
}
