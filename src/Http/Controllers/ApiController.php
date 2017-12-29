<?php

namespace ReaDev\ApiHelpers\Http\Controllers;

use Illuminate\Routing\Controller;
use ReaDev\ApiHelpers\Traits\ApiResponses;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends Controller
{
    use ApiResponses, AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
