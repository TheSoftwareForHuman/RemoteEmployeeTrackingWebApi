<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;

class RegisterRequest extends Request
{

  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    return [
      'name' => 'required',
      'login' => 'required|email|unique:users,email',
      'password' => 'required'
    ];
  }

}
