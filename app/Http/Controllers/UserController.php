<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Delete;
use Illuminate\Http\Request;
use App\Rules\MatchOldPassword;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $searched = false;

        if (isset($request->s_tk) && isset($request->user_name)) {
            $this->validate($request, [
                'user_name' => ['required', 'string'],
                's_tk' => ['required', 'string'],
            ], [
                'user_name.required' => 'खोजिको आधार राख्नुहोस्',
            ]);

            if ($request->s_tk !== session('_token')) {
                return back()->withInput();
            }

            $searched = true;
            $search_by = sanitize($request->user_name);
        }

        $userList = User::whereIn('user_type', ['2', '3', '4']);

        if ($searched) {
            $userList = $userList->where('name', 'like', '%' . $search_by . '%');
        }

        $userList = $userList->orderBy('id', 'desc')->paginate(50);

        return view('user.index', compact('userList'));
    }

    public function store(Request $request)
    {
        //validation
        $validatedData = $this->validate($request, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:5', 'confirmed'],
            'name' => ['required', 'string', 'max:255'],
            'post' => ['required', 'string', 'max:255'],
            'ins' => ['required', 'in:0,1'],
            'edit' => ['required', 'in:0,1'],
            'delete' => ['required', 'in:0,1'],
        ], [
            'email.required' => 'ईमेल राख्नुहोस्',
            'email.unique' => 'यो ईमेल पहिलेनै लिइएको छ।',
            'password.required' => 'पासवर्ड राख्नुहोस्',
            'password.min' => 'पासवर्ड कम्तिमा ५ अक्षरहरुको हुनुपर्दछ।',
            'password.confirmed' => 'पासवर्ड र पासवर्ड पुष्टि मिल्नु पर्दछ।',
            'name.required' => 'नाम , थर राख्नुहोस्',
            'sanket_no.required' => 'संकेत नम्बर राख्नुहोस्',
            'post.required' => 'पद छान्नुहोस्',
            'ins.required' => 'प्रविष्टि अधिकार छान्नुहोस्',
            'edit.required' => 'सम्पादन अधिकार छान्नुहोस्',
            'delete.required' => 'मेट्ने अधिकार छान्नुहोस्',
        ]);

        $validatedData['password'] = bcrypt($request->password);

        //insert data into database
        $addUser = new User();

        if ($addUser->create($validatedData)) {
            return redirect()->route('user.index')->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }
        return redirect()->route('user.index')->withError('तथ्यांक थप्न सकिएन।');

        /*
        $newUser = new User();
        $newUser->email = sanitize($request->username);
        $newUser->password = bcrypt($request->password);
        $newUser->user_type = sanitize($request->user_type);
        $newUser->name = sanitize($request->full_name);

        if ($newUser->save()) {
            return back()->withSuccess('तथ्यांक सफलतापूर्वक थप गरियो।');
        }

        */
    }

    public function reset_password($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('user.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting user data releted to id
        $userInfo = User::where('id', $id)->firstOrFail();

        return view('user.password-reset', compact('userInfo'));
    }

    public function update_password(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'password' => ['required', 'string', 'min:5', 'confirmed'],
        ], [
            'password.required' => 'पासवर्ड राख्नुहोस्',
            'password.min' => 'पासवर्ड कम्तिमा ५ अक्षरहरुको हुनुपर्दछ।',
            'password.confirmed' => 'पासवर्ड र पासवर्ड पुष्टि मिल्नु पर्दछ।',
        ]);

        $validatedData['password'] = bcrypt($request->password);

        //update data
        $editUser = User::where('id', $id)->firstOrFail();

        //return to user index page if user is updated
        if ($editUser->update($validatedData)) {
            $this->actionlog('App\Model\User', $id, 'Edit');
            return redirect()->route('user.index')->withSuccess('पासवर्ड सफलतापूर्वक परिवर्तन गरियो।');
        }
        return back()->withError('पासवर्ड परिवर्तन गर्न सकिएन।')->withInput();
    }

    public function change_password($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('user.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting user data releted to id
        $userInfo = User::where('id', $id)->firstOrFail();

        return view('user.password-change', compact('userInfo'));
    }

    public function updatemy_password(Request $request, $id, $hashtag)
    {

        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'old_password' => ['required', new MatchOldPassword],
            'password' => ['required', 'string', 'min:5', 'confirmed'],
        ], [
            'password.required' => 'पुरानो पासवर्ड राख्नुहोस्',
            'password.required' => 'नयाँ पासवर्ड राख्नुहोस्',
            'password.min' => 'नयाँ पासवर्ड कम्तिमा ५ अक्षरहरुको हुनुपर्दछ।',
            'password.confirmed' => 'नयाँ पासवर्ड र नयाँ पासवर्ड पुष्टि मिल्नु पर्दछ।',
        ]);

        // $validatedData['password'] = bcrypt($request->password);

        //update data
        $editUser = User::where('id', $id)->firstOrFail();

        //return to user index page if user is updated
        if ($editUser->update(['password' => bcrypt($request->password)])) {
            $this->actionlog('App\Model\User', $id, 'Edit');
            return redirect()->route('user.index')->withSuccess('पासवर्ड सफलतापूर्वक परिवर्तन गरियो।');
        }
        return back()->withError('पासवर्ड परिवर्तन गर्न सकिएन।')->withInput();
    }


    public function edit($id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return redirect()->route('user.index')->withError('अगाडि बढ्न सकिएन।');
        }

        //getting user data releted to id
        $userInfo = User::where('id', $id)->firstOrFail();

        return view('user.edit', compact('userInfo'));
    }

    public function update(Request $request, $id, $hashtag)
    {
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।')->withInput();
        }

        //validate data
        $validatedData = $this->validate($request, [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'name' => ['required', 'string', 'max:255'],
            'post' => ['required', 'string', 'max:255'],
            'ins' => ['required', 'in:0,1'],
            'edit' => ['required', 'in:0,1'],
            'delete' => ['required', 'in:0,1'],
        ], [
            'email.required' => 'ईमेल राख्नुहोस्',
            'email.unique' => 'यो ईमेल पहिलेनै लिइएको छ।',
            'name.required' => 'नाम , थर राख्नुहोस्',
            'sanket_no.required' => 'संकेत नम्बर राख्नुहोस्',
            'post.required' => 'पद छान्नुहोस्',
            'ins.required' => 'प्रविष्टि अधिकार छान्नुहोस्',
            'edit.required' => 'सम्पादन अधिकार छान्नुहोस्',
            'delete.required' => 'मेट्ने अधिकार छान्नुहोस्',
        ]);

        //update data
        $editUser = User::where('id', $id)->firstOrFail();

        // $editUser->email = sanitize($request->username);
        // $editUser->user_type = sanitize($request->user_type);
        // $editUser->name = sanitize($request->name);
        // $editUser->post = sanitize($request->post);
        // $editUser->ins = sanitize($request->ins);
        // $editUser->edit = sanitize($request->edit);
        // $editUser->delete = sanitize($request->delete);
        // $editUser->setup = sanitize($request->setup);
        // $editUser->data_entry = sanitize($request->data_entry);
        // $editUser->report = sanitize($request->report);
        // if ($editUser->save()) {
        //     return redirect()->route('user.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        // }

        //return to user index page if user is updated
        if ($editUser->update($validatedData)) {
            $this->actionlog('App\Model\User', $id, 'Edit');
            return redirect()->route('user.index')->withSuccess('तथ्यांक सफलतापूर्वक सम्पादन गरियो।');
        }
        return back()->withError('तथ्यांक सम्पादन गर्न सकिएन।')->withInput();
    }

    public function delete(Request $request)
    {
        $id = sanitize($request->id);
        $hashtag = sanitize($request->hashtag);
        //checking hashtag with id
        if (!checkHash($id, $hashtag)) {
            return back()->withError('अगाडि बढ्न सकिएन।');
        }


        //getting user data releted to id
        $user = User::where('id', $id)->firstOrFail();

        //check data exist into child table
        $relations = ['userlog'];

        if (Delete::check($user, $relations))
            if ($user->delete()) {
                $this->actionlog('App\Model\User', $id, 'Delete');
                return back()->withSuccess('तथ्यांक सफलतापूर्वक मेटाइयो।');
            }

        return back()->withError('तथ्यांक मेटाउन सकिएन।');
    }
}
