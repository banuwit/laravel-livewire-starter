# Remove Company Filter & Make Branch Filter Global Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the Company filter from the Users index view, and make the Branch filter stand on its own as a global filter loading all active branches.

**Architecture:** Remove the `$company_id` and `$companies` properties/logic from the Livewire component in `⚡index.blade.php` and update the view to display the Branch filter without any dependency on the Company filter.

**Tech Stack:** PHP, Laravel 10+, Livewire, Flux UI, Alpine.js, Tailwind CSS.

---

### Task 1: Modify Livewire Component Backend Logic

**Files:**
* Modify: [⚡index.blade.php](file:///d:/DEVELOPMENT/Learning/Laravel/laravel-livewire-starter/resources/views/pages/users/%E2%9A%A1index.blade.php)

- [ ] **Step 1.1: Remove company properties and update mount() to load all branches**
  Remove property declarations `$company_id` and `$companies` and load all branches globally on mount:
  ```php
  public string $search = '';
  public ?string $gender = null;
  public ?int $branch_id = null;
  public string $sortField = 'created_at';
  public string $sortDirection = 'desc';

  public ?int $deletingId = null;
  public string $deletingLabel = '';

  public array $branches = [];

  public function mount()
  {
      $this->branches = Branch::orderBy('name')->get(['id', 'name'])->toArray();
  }
  ```

- [ ] **Step 1.2: Remove updatedCompanyId hook and update updated lifecycle method**
  Remove the entire `updatedCompanyId` method and remove `company_id` from the array in `updated`:
  ```php
  public function updated($property): void
  {
      if (in_array($property, ['search', 'gender', 'branch_id'])) {
          $this->resetPage();
      }
  }
  ```

- [ ] **Step 1.3: Update render() query logic to remove company query filter**
  Update the query in the `render` method to remove `->when($this->company_id, ...)`:
  ```php
  $users = User::query()
      ->with(['company', 'branch', 'roles'])
      ->when($this->search, fn ($q) => $q->where(function ($q) {
          $q->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%');
      }))
      ->when($this->gender, fn ($q) => $q->where('gender', $this->gender))
      ->when($this->branch_id, fn ($q) => $q->where('branch_id', $this->branch_id))
      ->orderBy($this->sortField, $this->sortDirection)
      ->paginate(10);
  ```

- [ ] **Step 1.4: Run basic syntax/linter check**
  Run PHP linter to make sure there are no syntax errors:
  `php -l resources/views/pages/users/⚡index.blade.php`
  Expected: No syntax errors detected.

- [ ] **Step 1.5: Commit backend changes**
  ```bash
  git add resources/views/pages/users/⚡index.blade.php
  git commit -m "feat: remove company filter backend logic and load all branches"
  ```

---

### Task 2: Modify Users Blade View Frontend Logic

**Files:**
* Modify: [⚡index.blade.php](file:///d:/DEVELOPMENT/Learning/Laravel/laravel-livewire-starter/resources/views/pages/users/%E2%9A%A1index.blade.php)

- [ ] **Step 2.1: Remove Company select dropdown element**
  Delete the following block completely (lines 108-114):
  ```html
  <div class="w-48">
      <flux:select wire:model.live="company_id" variant="listbox" searchable clearable placeholder="Company">
          @foreach ($companies as $company)
              <flux:select.option value="{{ $company['id'] }}">{{ $company['name'] }}</flux:select.option>
          @endforeach
      </flux:select>
  </div>
  ```

- [ ] **Step 2.2: Update Branch select dropdown element**
  Remove `:disabled="!$company_id"` and change `:placeholder` to a static value of `"Branch"`:
  ```html
  <div class="w-48">
      <flux:select wire:model.live="branch_id" variant="listbox" searchable clearable placeholder="Branch">
          @foreach ($branches as $branch)
              <flux:select.option value="{{ $branch['id'] }}">{{ $branch['name'] }}</flux:select.option>
          @endforeach
      </flux:select>
  </div>
  ```

- [ ] **Step 2.3: Run basic syntax/linter check**
  Run PHP linter to make sure there are no syntax errors:
  `php -l resources/views/pages/users/⚡index.blade.php`
  Expected: No syntax errors detected.

- [ ] **Step 2.4: Commit frontend changes**
  ```bash
  git add resources/views/pages/users/⚡index.blade.php
  git commit -m "feat: remove company filter dropdown UI and update branch filter"
  ```
