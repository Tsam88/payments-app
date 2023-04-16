<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register new user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function register(Request $request)
    {
        // get the payload
        $data = $request->post();

        // register user
        $token = $this->userService->register($data);

        return new Response($token, Response::HTTP_OK);
    }

    /**
     * Login user (get token).
     *
     * @param Request $request
     *
     * @return Response
     */
    public function login(Request $request)
    {
        $data = $request->post();

        $token = $this->userService->login($data);

        return new Response($token, Response::HTTP_OK);
    }

    /**
     * Logout user.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function logout(Request $request)
    {
        // get user
        $user = $request->user();

        try {
            $this->userService->logout($user);
        } catch (\Exception $e) {
            // do nothing
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
