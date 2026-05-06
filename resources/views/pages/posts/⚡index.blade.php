<?php

use App\Models\Post;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Illuminate\Support\Str;

new class extends Component {
    use WithPagination, WithoutUrlPagination;

    public string $search = '';
    public ?string $status = null;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    public function deletePost(Post $post): void
    {
        $post->delete();
    }

    public function render()
    {
        $posts = Post::query()
            ->with(['author'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->status === 'published', fn ($q) => $q->published())
            ->when($this->status === 'draft', fn ($q) => $q->draft())
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
        return $this->view(['posts' => $posts]);
    }
};
?>

<div class="flex flex-col gap-4">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl">Posts</flux:heading>
        <flux:button wire:navigate href="{{ route('posts.create') }}" variant="primary" icon="plus">Add New</flux:button>
    </div>
    <div class="flex flex-col">
        <flux:card class="space-y-4" size="sm">
            <div class="flex items-center justify-between gap-4">
                <div class="flex gap-4">
                    <div class="w-72">
                        <flux:input icon="magnifying-glass" placeholder="Search title..." wire:model.live.debounce.300ms="search" clearable />
                    </div>
                    <div class="w-48">
                        <flux:select wire:model.live="status" variant="listbox" searchable clearable indicator="radio" placeholder="Status">
                            <flux:select.option value="published">Published</flux:select.option>
                            <flux:select.option value="draft">Draft</flux:select.option>
                        </flux:select>
                    </div>
                </div>
            </div>
            <flux:table :paginate="$posts" pagination:scroll-to>
                <flux:table.columns>
                    <flux:table.column sticky>#</flux:table.column>
                    <flux:table.column>
                        <flux:table.sortable :sorted="$sortField === 'title'" :direction="$sortField === 'title' ? $sortDirection : null" wire:click="sortBy('title')">
                            Title
                        </flux:table.sortable>
                    </flux:table.column>
                    <flux:table.column>Author</flux:table.column>
                    <flux:table.column>Excerpt</flux:table.column>
                    <flux:table.column>
                        <flux:table.sortable :sorted="$sortField === 'is_published'" :direction="$sortField === 'is_published' ? $sortDirection : null" wire:click="sortBy('is_published')">
                            Status
                        </flux:table.sortable>
                    </flux:table.column>
                    <flux:table.column>
                        <flux:table.sortable :sorted="$sortField === 'published_at'" :direction="$sortField === 'published_at' ? $sortDirection : null" wire:click="sortBy('published_at')">
                            Published
                        </flux:table.sortable>
                    </flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows wire:loading.class.remove="hidden" class="hidden">
                    @for ($i = 0; $i < 10; $i++)
                        <flux:table.row>
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900">
                                <flux:skeleton class="w-4 h-4" animate="pulse" />
                            </flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-32 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-24 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-48 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-16 h-6 rounded-full" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="w-20 h-4" animate="pulse" /></flux:table.cell>
                            <flux:table.cell><flux:skeleton class="size-8 rounded" animate="pulse" /></flux:table.cell>
                        </flux:table.row>
                    @endfor
                </flux:table.rows>

                <flux:table.rows wire:loading.remove>
                    @foreach ($posts as $post)
                        <flux:table.row>
                            <flux:table.cell sticky class="bg-white dark:bg-zinc-900">{{ $posts->firstItem() + $loop->index }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <flux:text variant="strong" class="font-semibold">{{ $post->title }}</flux:text>
                                    <flux:text variant="muted" class="text-xs">{{ $post->slug }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:avatar :name="$post->author->name" size="sm" />
                                    <flux:text variant="muted">{{ $post->author->name }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="max-w-xs truncate">{{ $post->excerpt ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($post->is_published)
                                    <flux:badge color="emerald">Published</flux:badge>
                                @else
                                    <flux:badge color="red">Draft</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $post->published_at ? $post->published_at->format('M d, Y') : '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" square />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:navigate href="{{ route('posts.edit', $post->id) }}">Edit</flux:menu.item>
                                        <flux:menu.item icon="trash" variant="danger" wire:click="deletePost({{ $post->id }})" wire:confirm="Are you sure you want to delete this post?">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>