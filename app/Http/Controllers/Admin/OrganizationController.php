<?php

namespace App\Http\Controllers\Admin;

use DB;
use App\User;
use App\Models\OrganizationSite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Panel\OrganizationSiteController;
use App\Http\Controllers\Panel\UserController as PanelUserController;
use App\Http\Controllers\Admin\UserController as AdminUserController;


class OrganizationController extends Controller
{

    private $organ_site_contrl;
    private $panel_user_contrl;
    private $admin_user_contrl;

    public function __construct(OrganizationSiteController $organ_site_contrl, PanelUserController $panel_user_contrl, AdminUserController $admin_user_contrl) {
        $this->organ_site_contrl = $organ_site_contrl;
        $this->panel_user_contrl = $panel_user_contrl;
        $this->admin_user_contrl = $admin_user_contrl;
    }


    // SITES
    public function listOrganizationSites() {
        return $this->organ_site_contrl->index();
    }
    public function createOrganizationSite() {
        return $this->organ_site_contrl->create();
    }
    public function storeOrganizationSite(Request $request) {
        return $this->organ_site_contrl->store($request);
    }
    public function editOrganizationSite($id) {
        return $this->organ_site_contrl->edit($id);
    }
    public function updateOrganizationSite(Request $request, $id) {
        return $this->organ_site_contrl->update($request, $id);
    }
    public function deleteOrganizationSite($id) {
        return $this->organ_site_contrl->destroy($id);
    }


    // MANAGE USERS
    public function manageUserLists(Request $request, $user_type, $returnManageDataOnly = false) {
        return $this->panel_user_contrl->manageUsers($request, $user_type, $returnManageDataOnly);
    }
    public function addNewUser($role_type) {
        return $this->admin_user_contrl->create($role_type);
    }


    // FOR SELECT DROPDOWNS
    public function fetchAjaxUsers(Request $request) {
        $search_term = $request['term'] ? $request['term'] : '';
        $user_type = $request['user_type'] ? $request['user_type'] : '';
        $organization_id = $request['organization_id'] ? $request['organization_id'] : 0;
        $organization_site_id = $request['organization_site_id'] ? $request['organization_site_id'] : 0;
        $results = [];
        if($user_type == 'organization' || $user_type == 'organization_manager' || $user_type == 'organization_sub_manager' || $user_type == 'user') {
            $results = User::where('role_name', $user_type)
                    ->where(function($query) use ($search_term) {
                        $query->where('full_name', 'LIKE', "%{$search_term}%")
                        ->orWhere('email', 'LIKE', "%{$search_term}%");
                    })->select('id', 'full_name', 'email')->get();

        } else if($user_type == 'organization_site') {
            $results = OrganizationSite::where('organ_id', $organization_id)
                    ->where('name', 'LIKE', "%{$search_term}%")
                    ->select('id', 'name')->get();

        } else if($user_type == 'organization_staff') {
            $results = DB::table('users')
                    ->where('users.role_name', $user_type)
                    ->leftjoin('organization_site_user', 'organization_site_user.user_id', 'users.id')
                    ->where('organization_site_user.organ_id', $organization_id)
                    ->where('organization_site_user.site_id', $organization_site_id)
                    ->where(function($query) use ($search_term) {
                        $query->where('users.full_name', 'LIKE', "%{$search_term}%")
                        ->orWhere('users.email', 'LIKE', "%{$search_term}%");
                    })->select('users.id', 'users.full_name', 'users.email')->get();
        } else {
            $results = [];
        }
        return $results;
    }

}
