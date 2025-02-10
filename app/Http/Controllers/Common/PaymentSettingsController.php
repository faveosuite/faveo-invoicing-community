<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentSettingsController extends Controller
{
    public function getPlugin()
    {
        $plugins = $this->fetchConfig();
        // dd($plugins);

        return \DataTables::of(new Collection($plugins))
                        // ->searchColumns('name')
                        ->addColumn('name', function ($model) {
                            return ucfirst($model['name']);
                        })
                        ->addColumn('description', function ($model) {
                            return $model['description'];
                        })
                        ->addColumn('author', function ($model) {
                            return ucfirst($model['author']);
                        })
                        ->addColumn('website', function ($model) {
                            return '<a href='.$model['website'].' target=_blank>'.$model['website'].'</a>';
                        })
                        ->addColumn('version', function ($model) {
                            return $model['version'];
                        })
                        ->addColumn('action', function ($model) {
                            if (array_has($model, 'path')) {
                                if ($model['status'] == 0) {
                                    $activate = '<form method="post" action='.url('plugin/status/'.$model['name']).'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                    <button type="submit" class="btn btn-secondary btn-sm btn-xs"'.tooltip('Activate').'<i class="fa fa-tasks" style="color:white;"></i></button></form>';
                                    $settings = ' ';
                                } else {
                                    $settings = '<a href='.url($model['settings']).' class="btn btn-secondary btn-sm btn-xs"'.tooltip('Settings').'<i class="nav-icon fa fa-fw fa-cogs" style="color:white;"></i></a>';
                                    $activate = '<form method="post" action='.url('plugin/status/'.$model['name']).'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                    <button type="submit" class="btn btn-secondary btn-sm btn-xs"'.tooltip('Deactivate').'<i class="fa fa-tasks" style="color:white;"></i></button></form>';
                                }

                                $delete = '<a href=  id=delete'.$model['name'].' class="btn btn-secondary btn-sm" data-toggle=modal data-target=#del'.$model['name']."><span style='color:white'><i class='fa fa-trash' style='color:white;'></i></span></a>"
                                        ."<div class='modal fade' id=del".$model['name'].">
                                            <div class='modal-dialog'>
                                                <div class=modal-content>  
                                                    <div class=modal-header>
                                                        <h4 class=modal-title>Delete</h4>
                                                    </div>
                                                    <div class=modal-body>
                                                       <p>Are you Sure ?</p>

                                                        <div class=modal-footer justify-content-between>
                                                            <button type=button class='btn btn-default pull-left' data-dismiss=modal id=dismis>".\Lang::get('lang.close').'</button>
                                                            <form method="delete" action='.url('plugin/delete/'.$model['name']).'>'.'<input type="hidden" name="_token" value='.\Session::token().'>'.'
                                    <button type="submit" class="btn btn-danger">Delete</button></form>
                                                            

                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                                $action = $settings.$activate;
                            } else {
                                $action = '';
                            }

                            return $action;
                        })
                      ->rawColumns(['name', 'description', 'author', 'website', 'version', 'action'])
                            ->make(true);
    }

    public function fetchConfig()
    {
        $configs = $this->readConfigs();

        $plugs = new Plugin();
        $fields = [];
        $attributes = [];
        if ($configs != 'null') {
            foreach ($configs as $key => $config) {
                $fields[$key] = include $config;
            }
        }

        if (count($fields) > 0) {
            foreach ($fields as $key => $field) {
                $plug = $plugs->where('name', $field['name'])->select(['path', 'status'])->orderBy('name');

                if ($plug) {
                    foreach ($plug as $i => $value) {
                        $attributes[$key]['path'] = $plug[$i]['path'];
                        $attributes[$key]['status'] = $plug[$i]['status'];
                    }
                } else {
                    $attributes[$key]['path'] = $field['name'];
                    $attributes[$key]['status'] = 0;
                }
                $attributes[$key]['name'] = $field['name'];
                $attributes[$key]['settings'] = $field['settings'];
                $attributes[$key]['description'] = $field['description'];
                $attributes[$key]['website'] = $field['website'];
                $attributes[$key]['version'] = $field['version'];
                $attributes[$key]['author'] = $field['author'];
            }
        }

        return $attributes;
    }

    public function readConfigs()
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR;
        $directories = scandir($dir);
        $files = [];
        foreach ($directories as $key => $file) {
            if ($file === '.' or $file === '..') {
                continue;
            }

            if (is_dir($dir.DIRECTORY_SEPARATOR.$file)) {
                $files[$key] = $file;
            }
        }
        //dd($files);
        $config = [];
        $plugins = [];
        if (count($files) > 0) {
            foreach ($files as $key => $file) {
                $plugin = $dir.$file;
                $plugins[$key] = array_diff(scandir($plugin), ['.', '..', 'ServiceProvider.php']);
                $plugins[$key]['file'] = $plugin;
            }
            foreach ($plugins as $plugin) {
                $dir = $plugin['file'];
                //opendir($dir);
                if ($dh = opendir($dir)) {
                    while (($file = readdir($dh)) !== false) {
                        if ($file == 'config.php') {
                            $config[] = $dir.DIRECTORY_SEPARATOR.$file;
                        }
                    }
                    closedir($dh);
                }
            }

            return $config;
        } else {
            return 'null';
        }
    }

    public function statusPlugin($slug)
    {
        $plugs = new Plugin();
        $plug = $plugs->where('name', $slug)->first();

        if (! $plug) {
            $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
            $str = "\n'App\\Plugins\\$slug"."\\ServiceProvider',";
            $line_i_am_looking_for = 102;
            $lines = file($app, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = $str;
            file_put_contents($app, implode("\n", $lines));
            $plugs->create(['name' => $slug, 'path' => $slug, 'status' => 1]);

            return redirect()->back()->with('success', __('message.status_changed'));
        }
        $status = $plug->status;

        if ($status == 0) {
            $plug->status = 1;

            $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
            $str = "\n'App\\Plugins\\$slug"."\\ServiceProvider',";
            $line_i_am_looking_for = 102;
            $lines = file($app, FILE_IGNORE_NEW_LINES);
            $lines[$line_i_am_looking_for] = $str;
            file_put_contents($app, implode("\n", $lines));
        } elseif ($status == 1) {
            $plug->status = 0;
            /*
             * remove service provider from app.php
             */
            $str = "\n'App\\Plugins\\$slug"."\\ServiceProvider',";
            $path_to_file = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';

            $file_contents = file_get_contents($path_to_file);
            $file_contents = str_replace($str, '//', $file_contents);
            file_put_contents($path_to_file, $file_contents);
        }

        $plug->save();

        return redirect()->back()->with('success', __('message.status_changed'));
    }

    public function postPlugins(Request $request)
    {
        $v = $this->validate($request, ['plugin' => 'required|mimes:application/zip,zip,Zip']);
        $plug = new Plugin();
        $file = $request->file('plugin');
        //dd($file);
        $destination = app_path().DIRECTORY_SEPARATOR.'Plugins';
        $zipfile = $file->getRealPath();
        /*
         * get the file name and remove .zip
         */
        $filename2 = $file->getClientOriginalName();
        $filename2 = str_replace('.zip', '', $filename2);
        $filename1 = ucfirst($file->getClientOriginalName());
        $filename = str_replace('.zip', '', $filename1);
        mkdir($destination.DIRECTORY_SEPARATOR.$filename);
        /*
         * extract the zip file using zipper
         */
        \Zipper::make($zipfile)->folder($filename2)->extractTo($destination.DIRECTORY_SEPARATOR.$filename);

        $file = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.$filename; // Plugin file path

        if (file_exists($file)) {
            $seviceporvider = $file.DIRECTORY_SEPARATOR.'ServiceProvider.php';
            $config = $file.DIRECTORY_SEPARATOR.'config.php';
            if (file_exists($seviceporvider) && file_exists($config)) {
                /*
                 * move to faveo config
                 */
                $faveoconfig = config_path().DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$filename.'.php';
                if ($faveoconfig) {
                    //copy($config, $faveoconfig);
                    /*
                     * write provider list in app.php line 128
                     */
                    $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
                    $str = "\n\n\t\t\t'App\\Plugins\\$filename"."\\ServiceProvider',";
                    $line_i_am_looking_for = 102;
                    $lines = file($app, FILE_IGNORE_NEW_LINES);
                    $lines[$line_i_am_looking_for] = $str;
                    file_put_contents($app, implode("\n", $lines));
                    $plug->create(['name' => $filename, 'path' => $filename, 'status' => 1]);

                    return redirect()->back()->with('success', __('message.installed_successfully'));
                } else {
                    /*
                     * delete if the plugin hasn't config.php and ServiceProvider.php
                     */
                    $this->deleteDirectory($file);

                    return redirect()->back()->with('fails', 'Their is no '.$file);
                }
            } else {
                /*
                 * delete if the plugin hasn't config.php and ServiceProvider.php
                 */
                $this->deleteDirectory($file);

                return redirect()->back()->with('fails', __('message.file_missing', ['file' => $file]));
            }
        } else {
            /*
             * delete if the plugin Name is not equal to the folder name
             */
            $this->deleteDirectory($file);

            return redirect()->back()->with('fails', '<b>'.__('messages.plugin_file_path_not_exist').'</b> '.$file);
        }
    }

    /**
     * Delete the directory.
     *
     * @param  type  $dir
     * @return bool
     */
    public function deleteDirectory($dir)
    {
        if (! file_exists($dir)) {
            return true;
        }
        if (! is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            chmod($dir.DIRECTORY_SEPARATOR.$item, 0777);
            if (! $this->deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) {
                return false;
            }
        }
        chmod($dir, 0777);

        return rmdir($dir);
    }

    /**
     * Reading the Filedirectory.
     *
     * @return type
     */
    public function readPlugins()
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins';
        $plugins = array_diff(scandir($dir), ['.', '..']);

        return $plugins;
    }

    public function deletePlugin($slug)
    {
        $dir = app_path().DIRECTORY_SEPARATOR.'Plugins'.DIRECTORY_SEPARATOR.$slug;
        $this->deleteDirectory($dir);
        /*
         * remove service provider from app.php
         */
        $str = "'App\\Plugins\\$slug"."\\ServiceProvider',";
        $path_to_file = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
        $file_contents = file_get_contents($path_to_file);
        $file_contents = str_replace($str, '//', $file_contents);
        file_put_contents($path_to_file, $file_contents);
        $plugin = new Plugin();
        $plugin = $plugin->where('path', $slug)->first();
        if ($plugin) {
            $plugin->delete();
        }

        return redirect()->back()->with('success', __('message.deleted-successfully'));
    }

    public function updatePaymentStatus(Request $request)
    {
        $plugs = new Plugin();
        $name = $request->input('name');
        $status = $request->input('status');
        $plug = $plugs->where('name', $name)->first();
        $app = base_path().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'app.php';
        $str = "\n'App\\Plugins\\$name"."\\ServiceProvider',";
        $line_i_am_looking_for = 102;
        $lines = file($app, FILE_IGNORE_NEW_LINES);
        $lines[$line_i_am_looking_for] = $str;
        if (! $plug) {
            file_put_contents($app, implode("\n", $lines));
            $plugs->create(['name' => $name, 'path' => $name, 'status' => 1]);

            return successResponse(\Lang::get('message.status_change'));
        }
        if ($status) {
            $plug->status = 1;
            file_put_contents($app, implode("\n", $lines));
        } else {
            $plug->status = 0;
            $file_contents = file_get_contents($app);
            $file_contents = str_replace($str, '//', $file_contents);
            file_put_contents($app, $file_contents);
        }
        $plug->save();

        return successResponse(\Lang::get('message.status_change'));
    }
}
