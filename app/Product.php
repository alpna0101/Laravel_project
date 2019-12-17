<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
    public function getUser()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    public function product_fileUpload($files)

    {

         $name = time()."_". $files->getClientOriginalName();
        $image = $files->move(public_path().'/uploads/product' , $name);
        return $name;
    }

    public function getImages(){
        return $this->hasMany('App\ProductImage');
    }
     public function getComments(){
        return $this->hasMany('App\ProductRating');
    }

}
