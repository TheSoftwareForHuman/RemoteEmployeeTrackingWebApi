<?php
namespace App\Http\Requests;

use Illuminate\Http\Request;

class LoginRequest extends Request
{

  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'login' => 'required',
      'password' => 'required'
    ];
  }

}