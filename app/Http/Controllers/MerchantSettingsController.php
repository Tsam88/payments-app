<?php

namespace App\Http\Controllers;

use App\Models\MerchantSetting;
use App\Services\MerchantSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MerchantSettingsController extends Controller
{
    /**
     * @var MerchantSettingsService
     */
    private $merchantSettingsService;

    public function __construct(MerchantSettingsService $merchantSettingsService)
    {
        $this->merchantSettingsService = $merchantSettingsService;
    }

    /**
     * Update merchant settings
     *
     * @param Request $request
     *
     * @return Response
     */
    public function update(Request $request, MerchantSetting $merchantSettings)
    {
        // get the payload
        $data = $request->post();

        // update merchant settings
        $this->merchantSettingsService->update($data, $merchantSettings);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
