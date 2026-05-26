# Remove Company Filter Design Spec

We are removing the **Company filter** from the Users index view and enabling a global, independent **Branch filter**.

## 1. Background & Context
Previously, the Users index view allowed filtering users by Company and then by Branch (which was dynamically loaded based on the selected Company). To simplify the user experience, the Company filter is being removed. The Branch filter will now load all branches and allow filtering by any branch directly.

## 2. Proposed Changes

### [MODIFY] [⚡index.blade.php](file:///d:/DEVELOPMENT/Learning/Laravel/laravel-livewire-starter/resources/views/pages/users/%E2%9A%A1index.blade.php)

#### Backend (Livewire Component)
* Remove `public ?int $company_id = null;` property.
* Remove `public array $companies = [];` property.
* Modify `mount()` to load all active branches from the database directly:
  ```php
  public function mount()
  {
      $this->branches = Branch::orderBy('name')->get(['id', 'name'])->toArray();
  }
  ```
* Remove the `updatedCompanyId($value)` hook since there is no company selection to listen to.
* Update the `updated($property)` lifecycle hook to remove the `'company_id'` check:
  ```php
  public function updated($property): void
  {
      if (in_array($property, ['search', 'gender', 'branch_id'])) {
          $this->resetPage();
      }
  }
  ```
* Update the `render()` method's query to remove the `company_id` filter check:
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

#### Frontend (Blade View Template)
* Delete the `<div class="w-48">` element containing the `company_id` filter `<flux:select>`.
* Modify the Branch `<flux:select>` filter:
  * Remove the `:disabled="!$company_id"` directive.
  * Update the placeholder from `:placeholder="!$company_id ? 'Select company first' : 'Branch'"` to a static `placeholder="Branch"`.

## 3. Verification Plan
* Ensure the Users index page loads without errors.
* Verify that the Company dropdown is no longer visible.
* Verify that the Branch dropdown is populated with all branches globally.
* Verify that selecting a branch successfully filters the users table to only users belonging to that branch.
* Ensure search, gender, and sorting filters still work correctly.
