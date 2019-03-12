<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\PasswordReset;

class UserController extends Controller
{
    /**
     * Send an password reset email
     *
     * @param Request $request
     * @return Response
     */
    public function requestPasswordReset(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return $this->sendError('User not found');
        }

        if ($user->generatePasswordResetMail()) {
            return $this->sendResponse([], 'Password reset requested sucessfully');
        } else {
            return $this->sendError('Something went wrong with the request, please try again.');
        }
    }

    /**
     * Change an user password based on a password reset request
     *
     * @param Request $request
     * @return Response
     */
    public function changeUserPassword(Request $request)
    {
        $passwordResetRequest = PasswordReset::where('token', $request->input('token'))->first();

        if (!$passwordResetRequest) {
            return $this->sendError('Password reset request not found');
        }

        $user = new User;
        $validator = Validator::make($request->all(), $user->getPasswordValidationRules()->rules, $user->getPasswordValidationRules()->messages);

        if ($validator->fails()) {
            return $this->sendError('Password change error', $validator->errors()->toArray(), 400);
        }

        if ($passwordResetRequest->redefineUserPassword($request->input('password'))) {
            return $this->sendResponse([], 'Password changed sucessfully');
        } else {
            return $this->sendError('Something went wrong with the password change, please try again.');
        }
    }

    /**
     * Retrieves a paginated list of users
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $items = $request->input('items') ? $request->input('items') : 25;
        $users = User::paginate($items);

        return $this->sendResponse($users, 'Users retrieved sucessfully');
    }

    /**
     * Retrieves an user by its ID
     *
     * @param [type] $id
     * @param Request $request
     * @return Response
     */
    public function find($id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found');
        }

        return $this->sendResponse($user, 'User retrieved sucessfully');
    }

    /**
     * Store a newly created user
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $user = new User;
        $validator = Validator::make($request->all(), $user->getStoreValidationRules()->rules, $user->getStoreValidationRules()->messages);

        if ($validator->fails()) {
            return $this->sendError('Creation error', $validator->errors()->toArray(), 400);
        }

        $user->fill([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $user->save();

        return $this->sendResponse($user, 'User created');
    }

    /**
     * Update a previously created user
     *
     * @param integer $id
     * @param Request $request
     * @return Response
     */
    public function update(int $id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found');
        }

        $validator = Validator::make($request->all(), $user->getUpdateValidationRules()->rules, $user->getUpdateValidationRules()->messages);

        if ($validator->fails()) {
            return $this->sendError('Creation error', $validator->errors()->toArray(), 400);
        }

        $user->fill([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        $user->update();

        return $this->sendResponse($user, 'User created');
    }

    /**
     * Soft deletes an user
     *
     * @param integer $id
     * @param Request $request
     * @return Response
     */
    public function delete(int $id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found');
        }

        $user->delete();

        return $this->sendResponse($user, 'Users deleted sucessfully');
    }
}
