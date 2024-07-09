<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSite;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class OrganizationSiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user = auth()->user();
        if(in_array($user->role_name, [Role::$organization, Role::$admin])) {
            if ($user->role_name === Role::$organization) {
                $organizationSites = OrganizationSite::with('organization')->where('organ_id', $user->id)->paginate(10);
                $data['organizationSites'] = $organizationSites;
                $template = getTemplate() . '.organization_site.index';
            } else {
                $organizationSites = OrganizationSite::with('organization')->paginate(10);
                $data['organizationSites'] = $organizationSites;
                $template = 'admin.manage.sites.list';
            }
            $organizations = User::where("role_name", "organization")->get();
            $data["user_type"] = "organization_site";
            $data["organizations"] = $organizations;
            return view($template, $data);
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $user = auth()->user();
        if(in_array($user->role_name, [Role::$organization, Role::$admin])) {
            if ($user->isAdmin()) {
                $organizations = User::where(['role_name'=> Role::$organization, 'status' => User::$active])->get();
            }

            $data = [
                'formAction' => route('panel.post.new.organizationSite'),
                'pageTitle' => trans('admin/main.organization_site_new_page_title'),
                'organizations' => isset($organizations) ? $organizations : null,
            ];
            if ($user->role_name === Role::$organization) {
                return view(getTemplate() . '.organization_site.create', $data);
            } else {
                $data['formAction'] = route('admin.organizations.sites.store');
                return view('admin.manage.sites.create', $data);
            }
        } else {
            abort(404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $user = auth()->user();
        if(in_array($user->role_name, [Role::$organization, Role::$admin])) {
            $name = $request->input('name');
            $organ_id = $request->input('organ_id');
            if (isset($name) && isset($organ_id)) {
                if(OrganizationSite::where(['name' => $name, 'organ_id' => $organ_id])->count()) {
                    return Redirect::back()->withErrors(['msg' => 'Another site with the same name already exists for this organization']);
                } else {
                    OrganizationSite::Create(['name' => trim($name), 'organ_id' => ($user->role_name === Role::$organization) ? $user->id : (int)$organ_id]);
                    if ($user->role_name === Role::$organization) {
                        return Redirect::route('panel.manage.organizationSites');
                    } else {
                        return Redirect::route('admin.organizations.sites');
                    }

                }
            } else {
                return Redirect::back()->withErrors(['msg' => 'Site and Organization name both are compulsory']);
            }
        } else {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $user = auth()->user();
        if(in_array($user->role_name, [Role::$organization, Role::$admin])) {
            if ($user->isAdmin()) {
                $organizationSite = OrganizationSite::find($id);
                $organizations = User::where(['role_name'=> Role::$organization, 'status' => User::$active])->get();
            } else {
                $organizationSite = OrganizationSite::where(['id' => $id, 'organ_id' => $user->id])->first();
            }

            $data = [
                'formAction' => route('panel.post.update.organizationSite', ['id' => $organizationSite->id]),
                'pageTitle' => trans('admin/main.organization_site_edit_page_title'),
                'organizationSite' => $organizationSite,
                'organizations' => isset($organizations) ? $organizations : null,
            ];
            if ($user->role_name === Role::$organization) {
                return view(getTemplate() . '.organization_site.create', $data);
            } else {
                $data['formAction'] = route('admin.organizations.sites.update', ['id' => $organizationSite->id]);
                return view('admin.manage.sites.create', $data);
            }
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = auth()->user();
        if(in_array($user->role_name, [Role::$organization, Role::$admin])) {
            $id = $request->input('id');
            $name = $request->input('name');
            $organ_id = $request->input('organ_id');
            if (isset($id ) && isset($name) && isset($organ_id)) {
                $organizationSite = OrganizationSite::find($id);
                if (isset($organizationSite) && $organizationSite->count()) {
                    if($user->role_name === Role::$organization && $user->id === $organizationSite->organ_id) {
                        $organizationSite->name = $name;
                        $organizationSite->save();
                    } elseif ($user->isAdmin()) {
                        $organizationSite->name = $name;
                        $organizationSite->organ_id = $organ_id;
                        $organizationSite->save();
                    }
                    if ($user->role_name === Role::$organization) {
                        return Redirect::route('panel.manage.organizationSites');
                    } else {
                        return Redirect::route('admin.organizations.sites');
                    }
                } else {
                    abort(404);
                }
            }
        } else {
            abort(404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403);
        }
        if(in_array($user->role_name, [Role::$organization, Role::$admin])) {
            $organizationSite = OrganizationSite::find($id);
            if($user->role_name === Role::$organization) {
                if($organizationSite->organ_id === $user->id) {
                    $organizationSite->delete();
                }
            } else {
                $organizationSite->delete();
                return Redirect::route('admin.organizations.sites');
            }
            return Redirect::route('panel.manage.organizationSites');
        } else {
            abort(404);
        }
    }
}
