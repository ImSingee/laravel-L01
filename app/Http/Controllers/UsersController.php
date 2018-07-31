<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\User;

use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['index', 'show', 'create', 'store']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        Auth::login($user);

        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
    }

    public function edit(User $user)
    {
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $e) {
            return abort(403, '无权访问');
        }

        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $e) {
            return abort(403, '无权访问');
        }

        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功');
        return redirect()->route('users.show', $user);
    }

    public function destroy(User $user)
    {
        try {
            $this->authorize('destroy', $user);
        } catch (AuthorizationException $e) {
            return abort(403, '无权访问');
        }
        $user->delete();
        session()->flash('success', '删除成功！');
        return redirect()->back();
    }
}
