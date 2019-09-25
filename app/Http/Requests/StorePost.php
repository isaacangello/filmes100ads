<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * config the validation messeges for each rules .
     *
     * @return array
     */
    public function messeges(){
        return [
            'required' => 'O campo :attribute deve ser preenchido',
            'image' => 'Houve algum problema com a imagem',
            'max' => 'a :attribute deve ter um tamanho máximo de 5mb ',
        ];

    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'require',
            'realname' => 'require',
            'sinopse' => 'require',
            'diretor' => 'require',
            'duracao' => 'require',
            'ano' => 'require',
            'pais' => 'require',
            'sinopse' => 'require',
            'capa' => 'require|image',
            'label1' => 'require',
            'url1' => 'require',
        ];
    }

}
