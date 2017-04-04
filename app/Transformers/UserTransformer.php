<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
      $formattedUser = [
          'id'                    => $user->id,
          'name'                  => $user->name,
          'email'                 => $user->email,
          'role'                  => $user->role,
          'birthdate'             => $user->birthdate,
          'active'                => (bool)$user->active,
          'createdAt'             => (string) $user->created_at,
          'updatedAt'             => (string) $user->updated_at
      ];

      return $formattedUser;
        //return $user->attributesToArray();
    }
}
