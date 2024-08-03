<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Modules\Essentials\Entities\EssentialsAdmissionToWork;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsEmployeeAppointmet;
use Modules\Essentials\Entities\EssentialsEmployeesContract;
use Modules\Essentials\Entities\EssentialsOfficialDocument;
use Modules\Essentials\Entities\WorkCard;
use App\Contact;
use Modules\Essentials\Entities\EssentialsEmployeeTravelCategorie;
use Modules\Essentials\Entities\EssentialsUserShift;
use Modules\Essentials\Entities\EssentialsWorkCard;
use Modules\HousingMovements\Entities\Car;
use Modules\InternationalRelations\Entities\IrProposedLabor;
use Modules\Sales\Entities\SalesProject;
use Spatie\Permission\Traits\HasRoles;
use Modules\Essentials\Entities\EssentialsEmployeesQualification;
use Modules\FollowUp\Entities\FollowupUserAccessProject;
use Modules\HelpDesk\Entities\HdTicket;
use Modules\HelpDesk\Entities\HdTicketReply;
use Modules\HousingMovements\Entities\HousingMovementsWorkerBooking;
use Modules\Essentials\Entities\EssentialsEmployeesInsurance;
use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use Modules\Essentials\Entities\UserLeaveBalance;
use Modules\HousingMovements\Entities\HtrRoom;
use Modules\HousingMovements\Entities\HtrRoomsWorkersHistory;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use HasApiTokens;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    /**
     * Get the business that owns the user.
     */



    public function appointment()
    {
        return $this->hasOne(EssentialsEmployeeAppointmet::class, 'employee_id')->where('is_active', 1);
    }

    public function userLeaveBalances()
    {
        return $this->hasMany(UserLeaveBalance::class, 'user_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Business::class);
    }

    public function essentialsEmployeesFamily()
    {
        return $this->hasMany(EssentialsEmployeesFamily::class, 'employee_id');
    }

    public function essentialsEmployeesInsurance()
    {
        return $this->hasOne(EssentialsEmployeesInsurance::class, 'employee_id');
    }


    public function company()
    {
        return $this->belongsTo(\App\Company::class);
    }
    public static function userTypes()
    {
        return User::distinct()
            ->pluck('user_type')
            ->reject(function ($value) {
                return $value == 'customer' || $value == 'customer_user' || $value == 'admin' || $value == 'user';
            })->toArray();
    }


    public function scopeUser($query)
    {
        return $query->where('users.user_type', 'LIKE', '%user%');
    }

    /**
     * The contact the user has access to.
     * Applied only when selected_contacts is true for a user in
     * users table
     */
    public function contactAccess()
    {
        return $this->belongsToMany(\App\Contact::class, 'user_contact_access');
    }

    /**
     * Get all of the users's notes & documents.
     */
    public function documentsAndnote()
    {
        return $this->morphMany(\App\DocumentAndNote::class, 'notable');
    }

    /**
     * Creates a new user based on the input provided.
     *
     * @return object
     */
    public static function create_user($details)
    {
        $user = User::create([
            'surname' => $details['surname'],
            'first_name' => $details['first_name'],
            'last_name' => $details['last_name'],
            'user_type' => $details['user_type'],
            'allow_login' => 1,
            'username' => $details['username'],
            'email' => $details['email'],
            'password' => Hash::make($details['password']),
            'created_by' => auth()->user()->id,
            'language' => !empty($details['language']) ? $details['language'] : 'en',
        ]);

        return $user;
    }

    /**
     * Gives locations permitted for the logged in user
     *
     * @param: int $business_id
     *
     * @return string or array
     */
    public function permitted_locations($business_id = null)
    {
        $user = $this;

        if ($user->can('access_all_locations')) {
            return 'all';
        } else {
            $business_id = !is_null($business_id) ? $business_id : null;
            if (empty($business_id) && auth()->check()) {
                $business_id = auth()->user()->business_id;
            }
            if (empty($business_id) && session()->has('business')) {
                $business_id = session('business.id');
            }

            $permitted_locations = [];
            $all_locations = BusinessLocation::where('business_id', $business_id)->get();
            $permissions = $user->permissions->pluck('name')->all();
            foreach ($all_locations as $location) {
                if (in_array('location.' . $location->id, $permissions)) {
                    $permitted_locations[] = $location->id;
                }
            }

            return $permitted_locations;
        }
    }

    /**
     * Returns if a user can access the input location
     *
     * @param: int $location_id
     *
     * @return bool
     */
    public static function can_access_this_location($location_id, $business_id = null)
    {
        $permitted_locations = auth()->user()->permitted_locations($business_id);

        if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
            return true;
        }

        return false;
    }

    public function scopeOnlyPermittedLocations($query)
    {
        $user = auth()->user();
        $permitted_locations = $user->permitted_locations();
        $is_admin = $user->hasAnyPermission('Admin#' . $user->business_id);
        if ($permitted_locations != 'all' && !$user->can('superadmin') && !$is_admin) {
            $permissions = ['access_all_locations'];
            foreach ($permitted_locations as $location_id) {
                $permissions[] = 'location.' . $location_id;
            }

            return $query->whereHas('permissions', function ($q) use ($permissions) {
                $q->whereIn('permissions.name', $permissions);
            });
        } else {
            return $query;
        }
    }

    /**
     * Return list of users dropdown for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     * @param $include_commission_agents = false (boolean)
     * @return array users
     */
    public static function forDropdown($business_id, $prepend_none = true, $include_commission_agents = false, $prepend_all = false, $check_location_permission = false)
    {
        $query = User::where('business_id', $business_id);

        if (!$include_commission_agents) {
            $query->where('is_cmmsn_agnt', 0);
        }

        if ($check_location_permission) {
            $query->onlyPermittedLocations();
        }

        $all_users = $query->select('id', DB::raw("CONCAT(COALESCE(first_name, ''),' ',COALESCE(mid_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"))->get();
        $users = $all_users->pluck('full_name', 'id');


        //Prepend none
        if ($prepend_none) {
            $users = $users->prepend(__('lang_v1.none'), '');
        }

        //Prepend all
        if ($prepend_all) {
            $users = $users->prepend(__('lang_v1.all'), '');
        }

        return $users;
    }

    /**
     * Return list of sales commission agents dropdown for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     * @return array users
     */
    public static function saleCommissionAgentsDropdown($business_id, $prepend_none = true)
    {
        $all_cmmsn_agnts = User::where('business_id', $business_id)
            ->where('is_cmmsn_agnt', 1)
            ->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"));

        $users = $all_cmmsn_agnts->pluck('full_name', 'id');

        //Prepend none
        if ($prepend_none) {
            $users = $users->prepend(__('lang_v1.none'), '');
        }

        return $users;
    }

    /**
     * Return list of users dropdown for a business
     *
     * @param $business_id int
     * @param $prepend_none = true (boolean)
     * @param $prepend_all = false (boolean)
     * @return array users
     */
    public static function allUsersDropdown($business_id, $prepend_none = true, $prepend_all = false)
    {
        $all_users = User::where('business_id', $business_id)
            ->select('id', DB::raw("CONCAT(COALESCE(surname, ''),' ',COALESCE(first_name, ''),' ',COALESCE(last_name,''),
            ' - ',COALESCE(id_proof_number,'')) as full_name"));

        $users = $all_users->pluck('full_name', 'id');

        //Prepend none
        if ($prepend_none) {
            $users = $users->prepend(__('lang_v1.none'), '');
        }

        //Prepend all
        if ($prepend_all) {
            $users = $users->prepend(__('lang_v1.all'), '');
        }

        return $users;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getUserFullNameAttribute()
    {
        return "{$this->surname} {$this->first_name} {$this->last_name}";
    }

    /**
     * Return true/false based on selected_contact access
     *
     * @return bool
     */
    public static function isSelectedContacts($user_id)
    {
        $user = User::findOrFail($user_id);

        return (bool) $user->selected_contacts;
    }

    public function getRoleNameAttribute()
    {
        $role_name_array = $this->getRoleNames();
        $role_name = !empty($role_name_array[0]) ? explode('#', $role_name_array[0])[0] : '';

        return $role_name;
    }

    public function media()
    {
        return $this->morphOne(\App\Media::class, 'model');
    }

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return \App\User
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get the contact for the user.
     */
    public function contact()
    {
        return $this->belongsTo(\Modules\Crm\Entities\CrmContact::class, 'crm_contact_id');
    }

    /**
     * Get the products image.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (isset($this->media->display_url)) {
            $img_src = $this->media->display_url;
        } else {
            $img_src = 'https://ui-avatars.com/api/?name=' . $this->first_name;
        }

        return $img_src;
    }
    public function country()
    {
        return $this->belongsTo(EssentialsCountry::class, 'nationality_id');
    }

    public function contract()
    {
        return $this->hasOne(EssentialsEmployeesContract::class, 'employee_id')->where('is_active', 1);
    }

    public function workCard()
    {

        return $this->hasOne(WorkCard::class, 'employee_id');
    }
    public function userContact()
    {
        return $this->belongsTo(Contact::class, 'assigned_to');
    }

    public function OfficialDocument()
    {
        return $this->hasMany(EssentialsOfficialDocument::class, 'employee_id')->where('is_active', 1);
    }



    public function proposal_worker()
    {
        return $this->belongsTo(IrProposedLabor::class, 'proposal_worker_id');
    }


    public function allowancesAndDeductions()
    {
        return $this->belongsToMany(EssentialsAllowanceAndDeduction::class, 'essentials_user_allowance_and_deductions', 'user_id', 'allowance_deduction_id');
    }



    public function assignedTo()
    {
        return $this->belongsTo(SalesProject::class, 'assigned_to');
    }



    public function rooms()
    {
        return $this->belongsTo(HtrRoom::class, 'room_id');
    }

    public function htrRoomsWorkersHistory()
    {
        return $this->hasMany(HtrRoomsWorkersHistory::class, 'worker_id');
    }


    public function calculateTotalSalary()
    {
        $allowances = $this->userAllowancesAndDeductions;


        $totalSalary = $this->essentials_salary;

        foreach ($allowances as $allowance) {
            if ($allowance->essentialsAllowanceAndDeduction !== null) {
                if ($allowance->essentialsAllowanceAndDeduction->type == 'deduction') {
                    $totalSalary -= $allowance->amount ?? 0;
                }
                if ($allowance->essentialsAllowanceAndDeduction->type == 'allowance') {
                    $totalSalary += $allowance->amount ?? 0;
                }
            }
        }
        return $totalSalary;
    }

    public function userAllowancesAndDeductions()
    {
        return $this->hasMany(\Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction::class, 'user_id');
    }

    public function essentials_admission_to_works()
    {
        return $this->hasOne(EssentialsAdmissionToWork::class, 'employee_id');
    }

    public function essentials_qualification()
    {
        return $this->hasOne(EssentialsEmployeesQualification::class, 'employee_id');
    }

    public function essentialsEmployeeAppointmets()
    {
        return $this->hasOne(EssentialsEmployeeAppointmet::class, 'employee_id')->where('is_active', 1);
    }
    public function essentialsworkCard()
    {
        return $this->hasOne(EssentialsWorkCard::class, 'employee_id');
    }

    public function allNotifications()
    {
        return $this->hasMany(Notification::class, 'notifiable_id');
    }

    public function Car()
    {
        return $this->belongsTo(Car::class);
    }

    public function employee_travle_categorie()
    {
        return $this->hasOne(EssentialsEmployeeTravelCategorie::class, 'employee_id');
    }
    public function leaveBalances()
    {
        return $this->hasMany(UserLeaveBalance::class);
    }
    public function essentialsUserShifts()
    {
        return $this->hasMany(EssentialsUserShift::class, 'user_id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'expense_for');
    }

    public function booking()
    {
        return $this->hasOne(HousingMovementsWorkerBooking::class, 'user_id');
    }
    public function tickets()
    {
        return $this->hasMany(HdTicket::class, 'user_id');
    }
    public function ticketReplies()
    {
        return $this->hasMany(HdTicketReply::class, 'user_id');
    }
    public function followupUserAccessProjects()
    {
        return $this->hasMany(FollowupUserAccessProject::class, 'user_id');
    }

    public function htrRoomsWorkersHistories()
    {
        return $this->hasMany(HtrRoomsWorkersHistory::class, 'worker_id', 'id');
    }


    public function sentNotificationsUser()
    {
        return $this->hasMany(SentNotificationsUser::class, 'user_id');
    }

    public function SentNotification()
    {
        return $this->hasMany(SentNotification::class, 'user_id');
    }

    public function activeContract()
    {
        return $this->hasOne(EssentialsEmployeesContract::class, 'employee_id')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeOfficialDocument()
    {
        return $this->hasMany(EssentialsOfficialDocument::class, 'employee_id')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activePassport()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'passport')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeResidencePermit()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'residence_permit')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeIban()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'Iban')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeNationalId()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'national_id')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeDriversLicense()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'drivers_license')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeCarRegistration()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'car_registration')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeInternationalCertificate()
    {
        return $this->hasOne(EssentialsOfficialDocument::class, 'employee_id')->where('type', 'international_certificate')->where('is_active', 1)->whereNotNull('file_path');
    }
    public function activeAppointmet()
    {
        return $this->hasOne(EssentialsEmployeeAppointmet::class, 'employee_id')->where('is_active', 1);
    }
    public function activeAdmission()
    {
        return $this->hasOne(EssentialsAdmissionToWork::class, 'employee_id')->where('is_active', 1);
    }

    public function userDevice()
    {
        return $this->hasOne(UserDevice::class, 'user_id');
    }
}
