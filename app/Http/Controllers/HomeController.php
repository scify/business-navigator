<?php

namespace App\Http\Controllers;

use App\BusinessLogicLayer\Aggregations;
/* For future reference:
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
*/
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * The ILT Index (aka Landing Page) invokable (single method) controller.
     *
     * The Index at the moment displays only a few counts of data, which
     * are passed via a simple view model, as the one described below.
     *
     * @return Response The Inertia Response.
     */
    public function __invoke(Aggregations $aggregations): Response
    {
        $count_aggregation = $aggregations->count()['total'];
        Log::debug('Home Controller Request', $count_aggregation);

        $viewModel = [
            'organisationCount' => $count_aggregation['organisations'],
            'countryCount' => $count_aggregation['countries'],
            'industryCount' => $count_aggregation['industries'],
        ];

        return Inertia::render('Home/Index/Page', $viewModel);
    }
}
