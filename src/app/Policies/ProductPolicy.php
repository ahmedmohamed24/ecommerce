<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @return mixed
     */
    public function viewAny(User $user)
    {
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return mixed
     */
    public function view(User $user, Product $product)
    {
    }

    /**
     * Determine whether the user can create models.
     *
     * @return mixed
     */
    public function create(User $user)
    {
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     *
     * @return mixed
     */
    public function update(Vendor $vendor, Product $product)
    {
        return $vendor->is($product->getOwner) || \auth('admin')->check();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return mixed
     */
    public function delete(Vendor $vendor, Product $product)
    {
        return $vendor->is($product->getOwner) || \auth('admin')->check();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return mixed
     */
    public function restore(Vendor $vendor, Product $product)
    {
        return $vendor->is($product->getOwner) || \auth('admin')->check();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Product $product)
    {
    }
}
