<?php

namespace App\Http\Controllers;

use App\Enums\UserRoleEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Admin: Display a listing of the resource.
     */
    public function index(){
        // For testing purposes
        // $test = $this->createRandomData();

        // Fetch all roles from the database
        $roles = Role::latest()->paginate();
        // Add metadata to the response
        $metadata = $roles;
        // Transform the items
        $data = RoleResource::collection($roles);
        // Return response
        return ApiResponse::success($data, 'successful', 200, $metadata);
    }

    /**
     * Admin: Create a new role
     */
    public function store(Request $request)
    {
        $data = $request->validate(
            [
            'name' => ['required', 'string','max:32', 'unique:roles,name'],
            // 'permissions' => ['required', 'array'],
            // 'permissions.*' => ['integer']
            ]
        );


        // This can also be removed
        // Check if the userRoleEnum has role
        if (!in_array($data['name'], UserRoleEnum::getValues())) {
            return ApiResponse::error([], 'Invalid role', 400);
        }

        // Check if the user has sufficient permissions to create a new role
        // if (!auth()->user()->can('create', Role::class)) {
        //     return ApiResponse::error('Unauthorized', 401);
        // }
        // Create a new role
        $role = Role::create($data);

        // Assign permissions to the role
        // $role->syncPermissions($request->permissions);
        // Or use the syncPermissions method with an array of permission IDs
        // $role->syncPermissions($request->permissions);
        
        // Return the newly created role
        return ApiResponse::success($role, 'Role created successfully', 201);
    }

    /**
     * Admin: update role
     */
    public function update(Request $request, Role $role)
    {
        // Validate request data
        $data = $request->validate(
            [
            'name' => ['required', 'string','max:32', 'unique:roles,name,' .$role->id],
            ]
        );

        // Update a new role
        $role = $role->update($data);

        // Return the updated role
        return ApiResponse::success($role, 'Role updated successfully', 200);

    }

    /**
     * Admin: show role
     */
    public function show(Role $role){
        // Return the role
        return ApiResponse::success($role, 'Role retrieved successfully', 200);
    }

    /**
     * Admin: Delete a role
     */
    public function destroy(Role $role){
        // Check if the userRoleEnum has role
        if (in_array($role->name, UserRoleEnum::getValues())) {
            return ApiResponse::error([], 'You can\'t delete master role', 401);
        }
        // Delete a role
        // Role::destroy($role);
        // Return response
        return ApiResponse::success([], 'Role deleted successfully', 200);
    }


    /**
     * Admin: Add permission to role
     */
    public function addPermission(Request $request, Role $role)
    {
        // Validate request data
        $data = $request->validate(
            [
            'permission' => ['required', 'integer', 'exists:permission,id'],
            ]
        );
        // Get permission information
        $permission = Permission::findOrFail($data['permission']);

        // Add a permission to a role
        $role->syncPermissions($permission->name);
        // Return response
        return ApiResponse::success([], 'Permission added to role', 200);
    }




    // This is a sample on how to create a new role with permissions
    private function assignRolePerm(){
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $userRole = Role::create(['name' => 'user']);

        // Create permissions
        $editPermission = Permission::create(['name' => 'edit articles']);
        $viewPermission = Permission::create(['name' => 'view articles']);

        // Assign permissions to roles
        $adminRole->givePermissionTo($editPermission, $viewPermission);
        $userRole->givePermissionTo($viewPermission);

        // Assign role to user
        $user = User::find(1); // Example user with ID 1
        $user->assignRole('admin');     
        

        // ['middleware' => ['role:admin']]
        // name('admin.dashboard')
        
        // ['middleware' => ['permission:edit articles']]
        // name('articles.edit')
    }
}
