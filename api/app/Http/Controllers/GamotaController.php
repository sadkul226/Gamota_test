<?php

namespace App\Http\Controllers;

use App\Gamota;
use App\User;
use Illuminate\Support\Facades\Input;

class GamotaController extends Controller
{

    // Define status with value success
    const STATUS_SUCCESS      = 'success';
    // Define type send email
    const SEND_TYPE_HALF_HOUR = '30m';
    const SEND_TYPE_1_DAY     = '1day';

    /**
     * Funtion get users most charge
     * 
     * @return json
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

    /**
     * Function send email to users
     * 
     * @return json
     */
    public function sendEmail()
    {
        $errors = [];
        $data   = [];
        if (Input::has('type')) {
            $type = Input::get('type');
            if ($type == self::SEND_TYPE_HALF_HOUR) {
                set_time_limit(60 * 30);
            } elseif ($type == self::SEND_TYPE_1_DAY) {
                set_time_limit(60 * 30 * 24);
            } else {
                $errors['error_code']    = 1;
                $errors['error_message'] = 'Type must be 30m or 1day';
            }
            if (!$errors) {
                $perPage     = 1000;
                $currentPage = 1;
                do {
                    \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($currentPage) {
                        return $currentPage;
                    });
                    $users = User::select('email')->paginate($perPage);

                    \Mail::raw('emails content', function ($msg) use ($users) {
                        $msg->from(config('mail.from.address'), config('mail.from.name'))
                            ->to($users->lists('email')->toArray(), 'Testing send email')
                            ->subject('sdsdsd');
                    });
                    if ($type == self::SEND_TYPE_HALF_HOUR) {
                        $flag = true;
                        while (!$flag) {
                            if (count(\Mail::failures())) {
                                $flag = false;
                                \Mail::raw('emails content', function ($msg) use ($users) {
                                    $msg->from(config('mail.from.address'), config('mail.from.name'))
                                        ->to(\Mail::failures(), 'Testing send email')
                                        ->subject('sdsdsd');
                                });
                            } else {
                                $flag = true;
                            }
                        }
                    }
                    $errors['error_code'] = 0;
                    $data                 = 'send email success';
                    $currentPage++;
                } while ($currentPage <= $users->lastPage());
            }
        } else {
            $errors['error_code']    = 2;
            $errors['error_message'] = 'Have not type';
        }

        return \Response::json([
                'errors' => json_encode($errors),
                'data'   => json_encode($data)
        ]);
    }

}
