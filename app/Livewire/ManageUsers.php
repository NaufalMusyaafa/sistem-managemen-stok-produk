<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ManageUsers extends Component
{
    public string $search = '';

    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public string $warehouse_id = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    public string $successMessage = '';

    protected function rules(): array
    {
        $uniqueEmail = $this->editingId
            ? 'unique:users,email,' . $this->editingId
            : 'unique:users,email';

        $passwordRule = $this->editingId ? 'nullable|min:6' : 'required|min:6';

        return [
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|' . $uniqueEmail,
            'password'     => $passwordRule,
            'role'         => 'required|in:admin_uid,admin_up3,rent,manager',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ];
    }

    protected $messages = [
        'name.required'     => 'Nama wajib diisi.',
        'email.required'    => 'Email wajib diisi.',
        'email.unique'      => 'Email sudah digunakan.',
        'password.required' => 'Password wajib diisi untuk user baru.',
        'password.min'      => 'Password minimal 6 karakter.',
        'role.required'     => 'Role wajib dipilih.',
    ];

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = ''; // Don't pre-fill password
        $this->role = $user->role;
        $this->warehouse_id = (string) ($user->warehouse_id ?? '');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->name,
            'email'        => $this->email,
            'role'         => $this->role,
            'warehouse_id' => $this->warehouse_id ?: null,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($data);
            $this->successMessage = 'User berhasil diperbarui.';
        } else {
            User::create($data);
            $this->successMessage = 'User berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $user = User::findOrFail($this->deletingId);
            if ($user->id === auth()->id()) {
                $this->successMessage = '⚠️ Anda tidak bisa menghapus akun sendiri.';
                $this->showDeleteModal = false;
                $this->deletingId = null;
                return;
            }
            $user->delete();
            $this->successMessage = 'User berhasil dihapus.';
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->warehouse_id = '';
        $this->resetValidation();
    }

    public function getFilteredUsersProperty()
    {
        return User::query()
            ->with('warehouse')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.manage-users', [
            'users'      => $this->filteredUsers,
            'warehouses' => Warehouse::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
