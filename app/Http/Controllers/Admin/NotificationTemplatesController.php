<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;

class NotificationTemplatesController extends Controller
{
    public function index()
    {
        $this->authorize('admin_notifications_list');

        $templates = NotificationTemplate::paginate(10);

        $data = [
            'pageTitle' => trans('admin/pages/users.templates'),
            'templates' => $templates
        ];

        return view('admin.notifications.templates', $data);
    }

    public function create()
    {
        $this->authorize('admin_notifications_template_create');

        $data = [
            'pageTitle' => trans('admin/pages/users.new_template'),
        ];

        return view('admin.notifications.new_template', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_notifications_template_create');
        $this->validate($request, [
            'title' => 'required',
            'template' => 'required',
        ]);

        $data = $request->all();

        // return $request;
        $template = $data['template'];
        // return $template;
                $dom = new \DomDocument();

                $dom->loadHtml($template, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                $imageFile = $dom->getElementsByTagName('img');

                foreach($imageFile as $item => $image){
                    $base64_data = $image->getAttribute('src');
                    //only proceed with image conversion and save attempt if base64 encoded pattern is encountered.
                    if (strpos($base64_data, ';base64,') !== false) {
                        list($type, $base64_data) = explode(';', $base64_data);
                        list(, $base64_data)      = explode(',', $base64_data);

                        //Image extension based on image type
                        list(, $imageExtension) = explode('/',$type);
                        $imageExtension = "." . $imageExtension;

                        $imageData = base64_decode($base64_data);

                        //create path if it doesn't exist
                        $subPath = '/store/webinars/1';
                        $path = public_path() . $subPath;
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        $image_name = time() . $item. $imageExtension;
                        return $image_name;
                        $path .= "/" . $image_name;
                        file_put_contents($path, $imageData);

                        $image->removeAttribute('src');
                        $image->setAttribute('src', $subPath . '/' . $image_name);
                    }
                    return $item;
                }
                $template = $dom->saveHTML();
                // return $template;

        NotificationTemplate::create([
            'title' => $data['title'],
            'template' => $template,
        ]);

        return redirect('/admin/notifications/templates');
    }

    public function edit($id)
    {
        $this->authorize('admin_notifications_template_edit');

        $template = NotificationTemplate::findOrFail($id);

        $data = [
            'pageTitle' => trans('admin/pages/users.edit_template'),
            'template' => $template
        ];

        return view('admin.notifications.new_template', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_notifications_template_edit');

        $this->validate($request, [
            'title' => 'required',
            'template' => 'required',
        ]);

        $data = $request->all();
        $template = NotificationTemplate::findOrFail($id);

        $template->update([
            'title' => $data['title'],
            'template' => $data['template'],
        ]);

        return redirect('/admin/notifications/templates');
    }

    public function delete($id)
    {
        $this->authorize('admin_notifications_template_delete');

        $template = NotificationTemplate::findOrFail($id);

        $template->delete();

        return redirect('/admin/notifications/templates');
    }
}
