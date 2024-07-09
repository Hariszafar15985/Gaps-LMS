<?php

namespace App\Traits;

use App\Models\OrganizationSite;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;

trait ajaxOrganizationUsersTrait
{

    public function fetchOrganizationSites(Request $request)
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isOrganization() || $user->isOrganizationManager() || $user->isOrganizationSubManager() ) {
            $organizationId = $request->get('organization');

            $sites = OrganizationSite::where('organ_id', $organizationId)
                ->pluck('id', 'name');

            $response = [
                'success' => true,
                'message' => 'Data Fetched',
                'site' => $sites,
            ];
            return response()->json($response);
        } else {
            $response = [
                'success' => false,
                'message' => 'Unauthorized Access'
            ];
            return response()->json($response);
        }
    }
    
    public function fetchSiteManagers(Request $request)
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isOrganization() || $user->isOrganizationManager() || $user->isOrganizationSubManager() ) {
            $organizationSite = $request->get('organization_site_id');

            $siteManagers = User::select('users.*')->where(['role_name' => Role::$organization_staff,
                'status' => User::$active])
                ->join('organization_site_user', 'organization_site_user.user_id', 'users.id')
                ->where('organization_site_user.site_id', $organizationSite)
                ->pluck('users.id', 'full_name');

            $response = [
                'success' => true,
                'message' => 'Data Fetched',
                'managers' => $siteManagers,
            ];
            return response()->json($response);
        } else {
            $response = [
                'success' => false,
                'message' => 'Unauthorized Access'
            ];
            return response()->json($response);
        }
    }    
}
