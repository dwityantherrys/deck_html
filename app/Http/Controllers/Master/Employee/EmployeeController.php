<?php

namespace App\Http\Controllers\Master\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\User;
use App\Models\Role;
use App\Models\Master\Profile\Profile;

class EmployeeController extends Controller
{
    private $route = 'master/employee';
    private $routeView = 'master.employee/employee';
    private $routeUpload = 'img/employee';
    private $params = [];
    private $roleNotDisplay;

    public function __construct ()
    {
      $this->model = new Profile();
      $this->roleNotDisplay = Role::whereIn('name', ['customer', 'employee'])->get();
      $this->params['route'] = $this->route;
      $this->params['routeView'] = $this->routeView;
    }

    /**
     * dipakai di menu purchase request
     */
    public function search(Request $request)
    {
      $where = "1=1";
      $response = [];

      if ($request->searchKey) {
        $where .= " and name like '%{$request->searchKey}%'";
      }

      try {
        // $results = $this->model->whereHas('user', function ($query) {
        //                     $query->whereNotIn('role_id', $this->roleNotDisplay->pluck('id'));
        //                 }
        // )->whereRaw($where)
        $results = User::whereRaw($where)
                   ->whereNotIn('role_id', $this->roleNotDisplay->pluck('id'))
                   ->get()
                   ->makeHidden(['created_at', 'updated_at']);

        $response['results'] = $results;
      } catch (\Exception $e) {
          report($e);
        return response(['message' => $e->print()], 500);
      }

      return response()->json($response, 200);
    }

    public function searchById($id)
    {
    //   return response()->json($this->model->find($id), 200);
      return response()->json(User::find($id), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->params['profiles'] = $this->model->whereHas('user', function ($query) {
                $query->whereNotIn('role_id', $this->roleNotDisplay->pluck('id'));
            }
        )->paginate(10);

        return view($this->routeView . '.index', $this->params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roleNotDisplay = ['customer', 'employee'];

        $this->params['roles'] = Role::whereNotIn('id', $this->roleNotDisplay->pluck('id'))->active()->get();
        $this->params['model'] = $this->model;

        return view($this->routeView . '.create', $this->params);
    }

    public function edit($id)
    {
        $profile = $this->model->find($id);
        $user = $profile->user()->first();

        $model = array_merge(
            $user->toArray(),
            $profile->toArray(),
            ['id' => $profile->id ] //initialisasi ulang, karena kereplace id trasaction_setting
        );

        $this->params['roles'] = Role::whereNotIn('id', $this->roleNotDisplay->pluck('id'))->active()->get();
        $this->params['model'] = (object) $model;

        return view($this->routeView . '.edit', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $validator = $this->_validate($request->all());
        //
        // if($validator->fails())
        // {
        //     return redirect()
        //         ->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        try {
            DB::beginTransaction();

            $image = NULL;
            if ($request->hasFile('image')) {
                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id
            ]);

            $profile = $user->profile()->create([
                'name' => $request->name,
                'image' => $image,
                'is_active' => $request->is_active
            ]);

            DB::commit();

            $request->session()->flash('notif', [
                'code' => 'success',
                'message' => str_replace(".", " ", $this->routeView) . ' success ' . __FUNCTION__ . 'd',
            ]);

            return redirect($this->route);

        } catch (\Throwable $th) {
            DB::rollback();

            if (!empty($image)) {
                \Storage::disk('public')->delete($image);
            }

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $validator = $this->_validate($request->all());
        //
        // if($validator->fails())
        // {
        //     return redirect()
        //         ->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        try {
            DB::beginTransaction();

            $profile = $this->model->find($id);
            $image = $profile->image;

            if ($request->hasFile('image')) {
                if ($image) {
                    \Storage::disk('public')->delete($image);
                }

                $image = $request->file('image')->store($this->routeUpload, 'public');
            }

            $profile->user()->update([
                'name' => $request->name,
                'email' => $request->email,
                'role_id' => $request->role_id
            ]);

            $profile->update([
                'name' => $request->name,
                'image' => $image,
                'is_active' => $request->is_active
            ]);

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
            $profile = $this->model->find($id);
            $userId = $profile->user_id;
            $image = $profile->image;

            $profile->delete();
            User::find($userId)->delete();

            if (!empty($image))
                \Storage::disk('public')->delete($image);

            DB::commit();
            return response()->json([], 204);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json($th, 500);
        }
    }

    private function _validate ($request)
    {
        $ignoredProfileId = !empty($request['id']) ? ','.$request['id'] : '';
        $ignoredUserId = !empty($request['id']) ? ','.$this->model->find($request['id'])->user_id : '';

        return Validator::make($request, [
            'name' => ['required'],
            'role_id' => ['required'],
            // 'email' => ['required', 'unique:users,email' . $ignoredUserId, 'email'],
        ]);
    }
}
