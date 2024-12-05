<?php

namespace App\Http\Controllers\Master\Employee;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Role;
use App\Models\RoleMenu;

class EmployeeRoleController extends Controller
{
    private $route = 'master/employee/role';
    private $routeView = 'master.employee.role';
    private $params = [];
    private $menus;

    public function __construct ()
    {
      $this->model = new Role();
      $this->menus = \Config::get('adminlte.menu');
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    public function search(Request $request)
    {
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " and name like '%{$request->searchKey}%'";
      }

      try {
        $results = $this->model->whereRaw($where)
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        $response['results'] = $results;
      } catch (\Exception $e) {
        return response(['message' => $e->getMessage()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById($id)
    {
      return response()->json($this->model->find($id), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $roleNotDisplay = ['customer', 'employee'];

      $this->params['roles'] = $this->model->whereNotIn('name', $roleNotDisplay)->get();
      return view($this->routeView . '.index', $this->params);
    }

    private function _menuByHeaders ()
    {
        $currentHeader = null;
        $menus = [];

        foreach ($this->menus as $menu) {
            if (is_string($menu)) {
                $currentHeader = Str::slug($menu, '_');
                $menus[$currentHeader] = [];
                continue;
            }

            $menus[$currentHeader][] = $menu;
        }

        return $menus;
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['menus'] = $this->_menuByHeaders();
        $this->params['model'] = $this->model;
        return view($this->routeView . '.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['name'] = Str::slug($request->display_name, '_');
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();
            $roleEmployee = $this->model->where('name', 'employee')->first()->id;

            $params = $request->all();
            $params['parent_id'] = $roleEmployee;
            $role = $this->model::create($params);

            if (count($params['menus']) > 0) {
                foreach ($params['menus'] as $menu) {
                    $role->menus()->create(['menu_key' => $menu]);
                }
            }
            
            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
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
        $this->params['menus'] = $this->_menuByHeaders();
        $this->params['model'] = $this->model->find($id);
        return view($this->routeView . '.edit', $this->params);
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
        $keepMenus = [];

        $request['name'] = Str::slug($request->display_name, '_');
        $validator = $this->_validate($request->all());

        if($validator->fails())
        {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $params = $request->all();
            unset($params['_token'], $params['_method'], $params['id']);
            $role = $this->model::where('id', $id)->first();
            $role->update($params);

            if (count($params['menus']) > 0) {
                foreach ($params['menus'] as $menu) {
                    $menuExist = $role->menus()->where('menu_key', $menu)->first();

                    if(!$menuExist) {
                        $newMenu = $role->menus()->create(['menu_key' => $menu]);
                        $keepMenus[] = $newMenu->id;
                        continue;
                    }

                    $keepMenus[] = $menuExist->id;
                }

                // delete yang tidak ada di request
                $role->menus()->whereNotIn('id', $keepMenus)->delete();
            }
            
            DB::commit();
            
            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);
            return redirect($this->route);

        } catch (\Throwable $th) {
            DB::rollback();

            $request->session()->flash('notif', [
                'code' => 'failed ' . __FUNCTION__ . 'd',
                'message' => str_replace(".", " ", $this->routeView) . ' : ' . $th->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput();
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
        try {
            DB::beginTransaction();
            $role = $this->model->find($id);

            $role->menus()->delete();
            $role->delete();

            DB::commit();
            
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    private function _validate ($request)
    {
        $ignoredId = !empty($request['id']) ? ','.$request['id'] : '';

        return Validator::make($request, [
            'name' => ['unique:roles,name' . $ignoredId],
            'display_name' => ['required'],
        ]);
    }
}
