<?php

use App\Models\Post;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:255')]
    public string $slug = '';

    #[Validate('nullable|string|max:500')]
    public ?string $excerpt = null;

    #[Validate('nullable|string')]
    public ?string $content = null;

    #[Validate('boolean')]
    public bool $is_published = false;

    public function updatedTitle($value): void
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'slug' => $this->slug ?: Str::slug($this->title),
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'is_published' => $this->is_published,
            'published_at' => $this->is_published ? now() : null,
            'author_id' => auth()->id(),
        ];

        Post::create($data);

        session()->flash('success', 'Post created successfully.');
        $this->redirectRoute('posts.index', navigate: true);
    }
};
?>

<div class="flex flex-col gap-6">
    <div class="flex items-center gap-3">
        <flux:button variant="ghost" icon="arrow-left" size="sm" square wire:navigate href="{{ route('posts.index') }}" />
        <div class="flex flex-col">
            <flux:heading size="xl">Create Post</flux:heading>
            <flux:text variant="muted">Write and publish a new post.</flux:text>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Post Information</flux:heading>
                    <flux:text variant="muted" size="sm">Title and URL slug.</flux:text>
                </div>
                <flux:separator />

                <flux:input wire:model.live.debounce.500ms="title" label="Title" placeholder="Enter post title" />
                <flux:input wire:model="slug" label="Slug" placeholder="auto-generated-from-title" description="Auto-generated, you can override." />
            </flux:card>

            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Content</flux:heading>
                    <flux:text variant="muted" size="sm">Body and short summary.</flux:text>
                </div>
                <flux:separator />

                <flux:textarea wire:model="excerpt" label="Excerpt" placeholder="Short summary of the post" rows="2" />
                <flux:textarea wire:model="content" label="Content" placeholder="Write your post content here..." rows="12" />
            </flux:card>
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="space-y-5">
                <div>
                    <flux:heading size="lg">Status</flux:heading>
                    <flux:text variant="muted" size="sm">Visibility of this post.</flux:text>
                </div>
                <flux:separator />

                <flux:checkbox wire:model="is_published" label="Published" description="Make this post visible to readers" />
            </flux:card>

            <flux:card class="space-y-3">
                <flux:button variant="primary" type="submit" class="w-full" icon="check">Save Post</flux:button>
                <flux:button wire:navigate href="{{ route('posts.index') }}" variant="ghost" class="w-full">Cancel</flux:button>
            </flux:card>
        </div>
    </form>
</div>
