<?php

namespace App\Http\Controllers;

use App\Gamota;
use Illuminate\Support\Facades\Input;

class GamotaController extends Controller
{
    // Defile constant status with value success
    const STATUS_SUCCESS = 'success';

    /**
     * Funtion get users most charge
     * 
     * @return list users
     */
    public function get()
    {
        $users  = collect();
        $errors = [];
        $query  = Gamota::where('status', self::STATUS_SUCCESS)
            ->where('target', '!=', '')
            ->groupBy('target')
            ->selectRaw('sum(amount) as total_amount, target, status, request_time, error_code')
            ->orderBy('total_amount', 'DESC');
        if (Input::has('limit')) {
            if (filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT)) {
                $query->limit(Input::get('limit'));
                $errors['error_code'] = 0;
                $users                = $query->get();
            } else {
                $errors['error_code'] = 1;
                $errors['error_msg']  = 'Param limit is not interger type';
            }
        } else {
            $errors['error_code'] = 0;
            $users                = $query->get();
        }

        return \Response::json([
                'errors' => json_encode($errors),
                'data'   => $users->toJson()
        ]);
    }

}
