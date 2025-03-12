<?php

use App\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


#Signup
Route::post('/signup',[AdminController::class , 'signUp']);

#Login
Route::post('/login',[AdminController::class , 'login']);

#Profile
Route::middleware('auth:sanctum')->get('/profile', [AdminController::class, 'profile']);

#Show All Product
Route::middleware('auth:sanctum')->get('/viewallproduct', [AdminController::class, 'viewAllProduct']);

#Add To Cart
Route::middleware('auth:sanctum')->post('/addcart/{id}', [AdminController::class, 'addToCart']);
// Route::post('/addcart/{id}',[AdminController::class , 'addToCart']);

#Get Cart Product
Route::middleware('auth:sanctum')->get('/getcartproduct', [AdminController::class, 'getCartProduct']);
// Route::get('/getcartproduct',[AdminController::class , 'getCartProduct']);

#Update Product
Route::middleware('auth:sanctum')->post('/updatequantity', [AdminController::class, 'updateQuantity']);

#Remove product from cart
Route::middleware('auth:sanctum')->delete('/removecart/{id}', [AdminController::class, 'removeCart']);

#Get cart product to checkout
Route::middleware('auth:sanctum')->get('/getcheckoutproduct', [AdminController::class, 'checkoutProduct']);


// DASHBOARD

Route::get('/testapigetuser',[AdminController::class , 'allUser']);

#Insert Product
Route::post('/addproduct',[AdminController::class , 'addProduct']);

#View Product
Route::get('/viewallproduct',[AdminController::class , 'viewAllProduct']);

#Delete Product
Route::delete('/deleteproduct/{id}',[AdminController::class , 'deleteProduct']);

#Get 1 Product
Route::get('/getproduct/{id}',[AdminController::class , 'getProduct']);

#Update Product
Route::post('/updateproduct/{id}',[AdminController::class , 'updateProduct']);

#Search Product
Route::get('/searchproduct/{key}',[AdminController::class , 'searchProduct']);

