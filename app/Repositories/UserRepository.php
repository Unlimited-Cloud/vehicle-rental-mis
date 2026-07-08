<?php

namespace App\Repositories;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Models\PasswordLog;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Partners;
use App\Models\Role;
use App\Models\Partnerdetailstable;
use Illuminate\Support\Facades\Auth;
use App\Models\PartnerUser;
use App\Models\CrewProfile;
use App\Models\VehicleOwner;

class UserRepository implements UserRepositoryInterface
{
    public function getAllUsers($search)
    {
        $query = User::with('role');
        $query =   DB::table('users')
            ->select(
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.password',
                'users.id',
                'users.email',
                'users.phone_no',
                'users.role_id',
                'users.status',
                'users.organization_id',
            );


        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate(10);
    }

    public function storeUser($data)
    {

        $userdata = [
            'name' => $data['name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'phone_no' => $data['phone_no'],
            'role_id' => $data['roles'],
            'status' => $data['status'],
            'organization_id' => $data['organization_id']
        ];

        $user =  User::create($userdata);



        // Store password in password_logs if user creation is successful
        if ($user) {
            PasswordLog::create([
                'customer_type' => 'User', // Adjust this if customer type is available in $data
                'customer_id' => $user->id, // Storing user ID as customer ID
                'first_password' => $userdata['password'], // Already hashed
                'first_password_created_at' => now(),
                'second_password' => null,
                'second_password_created_at' => null,
                'third_password' => null,
                'third_password_created_at' => null,
            ]);
        }


        return $user;
        //    $patnerData = [
        //     'legal_name'=>$data['legal_name'],
        //     'short_name'=>$data['short_name'],
        //     'doing_business_as'=>$data['doing_business_as'],
        //     'abn'=>$data['abn'],
        //     'user_id'=>$user->id,
        //     'tax_registration_number'=>$data['tax_registration_number'],
        //     'business_type'=>$data['business_type'],
        //     'industry_type'=>$data['industry_type'],
        //     'address'=>$data['address'],
        //     'country_id'=>$data['country_id'],
        //     'state'=>$data['state'],
        //     'city'=>$data['city'],
        //     'address_line_1'=>$data['address_line_1'],
        //     'address_line_2'=>$data['address_line_2'],
        //     'zip_code'=>$data['zip_code'],
        //     'notification_setting'=>$data['notification_setting'],
        //     'support_phone_no'=>$data['support_phone_no'],
        //     'support_email'=>$data['support_email'],
        //     'website_url'=>$data['website_url']
        //    ];

        //    Partnerdetailstable::create($patnerData);
    }


    public function updateUser($data, $id)
    {

        $userdata = [
            'name' => $data['name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],

            'role_id' => $data['role_id'],
            'status' => $data['status'],
            'organization_id' => $data['organization_id']
        ];

        User::where('id', $id)->update($userdata);
    }

    public function deleteUser($id)
    {

        User::where('id', $id)->delete();
        Partnerdetailstable::where('user_id', $id)->delete();
    }

    public function getAllPartners()
    {
        return Partners::all();
    }

    public function getUser($id)
    {

        return  User::select(
            'users.id',
            'users.user_uuid',
            'users.name',
            'users.middle_name',
            'users.last_name',
            'users.email',
            'users.password',
            'users.phone_number_country_code',
            'users.phone_no',
            'users.role_id',
            'users.status',
            'users.organization_id',
            'users.dob',
            'users.resident_country',
            'users.nationality',
            'users.postal_code',
            'users.street_address',
            'users.street_address_2',
            'users.city',
            'users.designation',
            'users.kyc_status',
            'roles.name as role_name',
        )
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $id)
            ->first();
    }


    public function getAllPermissions()
    {
        $query =  DB::table('permissions')
            ->select(
                'permissions.id as id',
                'modules.name as module_name',
                'permissions.name',
                'permissions.created_at'

            )
            ->join(
                'modules',
                'permissions.module_id',
                'modules.id'
            );

        // if ($query) {
        //     $query->where(function ($q) use ($search) {
        //         $q->Where('permissions.name', 'like', '%' . $search . '%');
        //     });
        // }
        return $query->get();
    }


    public function getAllPermissionWithPagination()
    {

        $query =  DB::table('permissions')
            ->select(
                'permissions.id as id',
                'modules.name as module_name',
                'permissions.name',
                'permissions.created_at'

            )
            ->join(
                'modules',
                'permissions.module_id',
                'modules.id'
            );

        // if ($query) {
        //     $query->where(function ($q) use ($search) {
        //         $q->Where('permissions.name', 'like', '%' . $search . '%');
        //     });
        // }
        return $query->paginate(10);
    }

    public function getAllRoles()
    {

        return Role::all();;
    }


    public function savePermission($permission)
    {

        Permission::create($permission);
    }

    public function getPermissionById($id)
    {
        return Permission::where('id', $id)->first();
    }
    public function updatePermission($id, $permission)
    {
        Permission::where('id', $id)->update($permission);
    }

    public function deletePermission($id)
    {
        Permission::where('id', $id)->delete();
    }


    public function getRolesWithPermissions($id)
    {

        return DB::table('permission_role')->where('role_id', $id)->get();
    }

    public function getAllUsersList($search)
    {
        $query = User::with('role');
        $query =   DB::table('users')
            ->select(
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.password',
                'users.id',
                'users.email',
                'users.phone_no',
                'roles.name as role_name',
                DB::raw("CASE
                    WHEN users.status = 1
                    THEN 'Active'
                    ELSE 'Inactive'
                    END as status"),
                DB::raw("CASE
                    WHEN users.organization_id = 1 THEN 'Company'
                    ELSE 'Other Organization'
                   END as organization_id")

            )

            ->leftjoin('roles', 'users.role_id', '=', 'roles.id');


        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%');
            });
        }

        return $query->paginate(10);
    }

    public function ProfileDetails()
    {
        return   DB::table('users')
            ->select(
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.password',
                'users.id',
                'users.email',
                'users.phone_no',
                'users.created_at',
                'roles.name as role_name',
                DB::raw("CASE
                        WHEN users.status = 1
                        THEN 'Active'
                        ELSE 'Inactive'
                        END as status"),
                DB::raw("CASE
                        WHEN users.organization_id = 1 THEN 'Company'
                        ELSE 'Other Organization'
                       END as organization_id")

            )

            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', Auth::user()->id)
            ->first();
    }

    public function updateStatus($toggelval, $userId)
    {
        User::where('id', $userId)->update(['status' => $toggelval]);
        return User::where('id', $userId)->value('name');
    }

    public function getAllPartnerUsersList($search, $partnerFilter = null)
    {
        $query = User::with('role');
        $query = DB::table('users')
            ->select(
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.password',
                'users.id',
                'users.email',
                'users.phone_number_country_code',
                'users.phone_no',
                'roles.name as role_name',
                DB::raw("CASE
                    WHEN users.status = 1
                    THEN 'Active'
                    ELSE 'Inactive'
                    END as status"),
                DB::raw("CASE
                    WHEN users.organization_id = 1 THEN 'Company'
                    ELSE 'Other Organization'
                   END as organization_id"),
                'partners.name as partnername'
            )
            ->join('partner_user', 'partner_user.user_id', '=', 'users.id')
            ->join('partners', 'partner_user.partner_id', '=', 'partners.id')
            ->leftjoin('roles', 'users.role_id', '=', 'roles.id');


        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%');
            });
        }

        if ($partnerFilter) {
            $query->where('partners.name', 'like', '%' . $partnerFilter . '%');
        }



        return $query->paginate(10);
    }

    public function storePartnerUser($data)
    {

        $userdata = [
            'name' => $data['name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'phone_number_country_code' => $data['phone_number_country_code'],
            'phone_no' => $data['phone_no'],
            'role_id' => $data['roles'],
            'status' => $data['status'],
        ];

        $user =  User::create($userdata);

        return $user;
    }

    /**
     * @author prabinunlimited
     * Get All Users except the user associated with Partner
     * @return User Data
     */
    public function getAllNonPartnerUsersList($search)
    {
        $query = User::with('role');
        $query =   DB::table('users')
            ->select(
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.password',
                'users.id',
                'users.email',
                'users.phone_no',
                'roles.name as role_name',
                DB::raw("CASE
                    WHEN users.status = 1
                    THEN 'Active'
                    ELSE 'Inactive'
                    END as status"),
                DB::raw("CASE
                    WHEN users.organization_id = 1 THEN 'Company'
                    ELSE 'Other Organization'
                   END as organization_id")

            )

            ->leftjoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereNotIn('users.id', function ($query) {
                $query->select('user_id')->from('partner_user');
            });


        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('users.name', 'like', '%' . $search . '%');
            });
        }
        $query->orderBy('users.name');
        return $query->paginate(10);
    }

    public function getAllPartnerRoles()
    {

        return Role::where('is_partner', 1)->get();
    }

    /**
     * @author prabinunlimited
     * Get All logged in Partner Users List
     * @return user data
     */
    public function getAllLoggedInPartnerUsersList($search, $partnerId, $partnerFilter = null)
    {
        $query = DB::table('users')
            ->select(
                'users.name',
                'users.middle_name',
                'users.last_name',
                'users.password',
                'users.id',
                'users.email',
                'users.phone_number_country_code',
                'users.phone_no',
                'roles.name as role_name',
                DB::raw("CASE
                WHEN users.status = 1 THEN 'Active'
                ELSE 'Inactive'
            END as status"),
                DB::raw("CASE
                WHEN users.organization_id = 1 THEN 'Company'
                ELSE 'Other Organization'
            END as organization_type"),
                'partners.name as partnername'
            )
            ->join('partner_user', 'partner_user.user_id', '=', 'users.id')
            ->join('partners', 'partner_user.partner_id', '=', 'partners.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where('partners.id', $partnerId);

        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.phone_no', 'like', "%{$search}%");
            });
        }

        if (!empty($partnerFilter)) {
            $query->where('partners.name', 'like', "%{$partnerFilter}%");
        }

        return $query->paginate(10); // ✅ now pagination works with ->links()
    }

    public function getLoggedInPartnerData($partnerId)
    {
        return Partners::where('id', $partnerId)->get();
    }

    /**
     * @author prabinunlimited
     * Get Partner and User Detail by User Id
     * @return user and partner data
     */
    public function getPartnerUser($id)
    {
        $data = User::select(
            'users.name',
            'users.name as first_name',
            'users.middle_name',
            'users.last_name',
            'users.id',
            'users.email',
            'users.password',
            'users.phone_number_country_code',
            'users.phone_no',
            DB::raw("CONCAT(users.phone_number_country_code, users.phone_no) as full_mobile_number"),
            'users.role_id',
            'users.status',
            'users.organization_id',
            'partner_user.partner_id',
            'roles.name as role_name',
            DB::raw("CASE
                    WHEN users.status = 1
                    THEN 'Active'
                    ELSE 'Inactive'
                    END as status"),
            DB::raw("CASE
                 WHEN users.organization_id = 1 THEN 'Company'
                 ELSE 'Other Organization'
                END as organization_id"),
            'users.client_user_id',
            'partnerdetailstables.dob'
        )
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('partner_user', 'partner_user.user_id', '=', 'users.id')
            ->join('partners', 'partners.id', '=', 'partner_user.partner_id')
            ->leftJoin('partnerdetailstables', 'partnerdetailstables.partner_id', '=', 'partners.id')
            ->where('users.id', $id)
            ->first();

        return $data;
    }

    public function updatePartnerUser($data, $id)
    {

        $userdata = [
            'name' => $data['name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number_country_code' => $data['phone_number_country_code'],
            'phone_no' => $data['phone_no'],
            'role_id' => $data['role_id'],
            'status' => $data['status'],
        ];

        User::where('id', $id)->update($userdata);

        PartnerUser::updateOrCreate(
            ['user_id' => $id],
            ['partner_id' => $data['partner_id']]
        );
    }

    /**
     * @author prabinunlimited
     * Check the logged in user belong to white labelled partner
     * @return Yes => Y | No => N
     */
    public function checkIsWhiteLabelledPartnerUser($userId)
    {
        $partnerDetail = PartnerUser::where('partner_user.user_id', $userId)
            ->join('partners', 'partner_user.partner_id', '=', 'partners.id')
            ->join('partner_types', 'partners.partner_type', '=', 'partner_types.id')
            ->where('partner_types.name', 'whitelabeled')
            ->first();
        if ($partnerDetail) {
            return "Y";
        } else {
            return "N";
        }
    }

    /**
     * @author prabinunlimited
     * Get User By Email
     * @param email user email
     * @return User Detail
     */
    public function getUserByEmail($email)
    {
        $detail = User::where('email', $email)->first();
        return $detail;
    }

    /**
     * @author prabinunlimited
     * Get Customer User By Email
     * @param email user email
     * @return User Detail
     */
    public function getCustomerUserByEmail($email)
    {
        $detail = User::select('users.*')
            ->where('users.email', $email)->join('customer_users', 'customer_users.user_id', '=', 'users.id')
            ->first();
        return $detail;
    }

    public function getRoleById($id)
    {
        return Role::where('id', $id)->first();
    }

    /**
     * @author prabinunlimited
     * Get User By UUID
     * @param uuid user uuid
     * @return User Detail
     */
    public function getUserByUuid($uuid)
    {
        $detail = User::where('user_uuid', $uuid)->first();
        return $detail;
    }

    public function getUsers()
    {
        return User::latest()
            ->select('users.*', 'roles.name as rolename')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->get();
    }

    public function getUsersByCustomerId($customerId)
    {
        return User::where('customer_id', $customerId)->latest()
            ->select('users.*', 'roles.name as rolename')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->get();
    }

    public function getUserByCustomerIdAndUserType($customerId, $userType)
    {
        return User::where('customer_id', $customerId)
            ->where('users.user_type', 'customer_app')->latest()
            ->select('users.*', 'roles.name as rolename')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->first();
    }

    public function getCrewProfileByUserId($userId)
    {
        return CrewProfile::where('user_id', $userId)->first();
    }

    public function getVehicleOwnerByUserId($userId)
    {
        return VehicleOwner::where('user_id', $userId)->first();
    }
}
