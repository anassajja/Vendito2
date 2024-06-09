<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; //import DB facade

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::all();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  

        return response()->json([
            'message' => 'Offers fetched successfully',
            'offers' => $offers
        ], 200);
    }

    public function show($id) { //get offer by id
        
        $offer = Offer::find($id);

        if ($offer === null) {
            return response()->json([
                'message' => 'No offer found'
            ], 404);
        }  

        return response()->json([
            'message' => 'Offer fetched successfully',
            'offer' => $offer
        ], 200);
    }

    public function store(Request $request)
    {
        $user = auth()->user(); //get authenticated user

        $request->validate([
            'title' => 'required',
            'description' => 'required|min:10|max:255',
            'price' => 'required|numeric',
            'image'=> 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', //validate image
            'category' => 'required',
            'location' => 'required',
            'condition' => 'required',
            'delivery' => 'required',
            'negotiable',
            'phone' => 'required|numeric', //validate phone number
            'status',
            'user_id',
        ]); //validate request
        
        if ($user) {
            $request['user_id'] = $user->id; //add user id to request

            $offer = Offer::create($request->all()); //create offer

            if ($request->hasFile('image')) {
                $image = $request->file('image'); // get image name from request
                $name = time() . '.' . $image->getClientOriginalExtension(); //generate image name
                $image->isValid() ? true : false; //check if image is valid
                $destinationPath = public_path('/images'); //path to save images
                $image->move($destinationPath, $name);  //move image to path
                $offer->image = $name; //update offer with new image name
                $offer->save(); //save the offer
            }

            else {
                $request['image'] = ''; //set image to empty string
                return $request; //return request
            }

            return response()->json([
                'message' => 'Offer created successfully',
                'offer' => $offer
            ], 201);

        } else {
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
        }

        // The "401 Unauthorized" status code indicates that the request lacks valid authentication credentials. On the other hand, the "403 Forbidden" status code signifies that the server understands the request but refuses to fulfill it.
    }

    public function update(Request $request, $id)
    {
       $user = auth()->user(); //get authenticated user
    
       $offerToUpdate = Offer::find($id);
    
        if ($offerToUpdate === null) { //if offer is not found
            return response()->json([
                'message' => 'No offer found'
            ], 404);
        }  
    
        $offerToUpdate->update($request->all()); //update offer
    
        return response()->json([
            'message' => 'Offer updated successfully',
            'offer' => $offerToUpdate
        ], 200);
    }

    public function delete($id)
    {
        $admin = auth()->user(); //get authenticated user

        if ($admin->role !== 'admin') { //check if user is admin
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        else { //if user is admin 
            $offer = Offer::find($id);

            if ($offer === null) { //if offer is not found
                return response()->json([
                    'message' => 'No offer found'
                ], 404);
            }  

            $offer->delete();

            return response()->json([
                'message' => 'Offer deleted successfully'
            ], 200);
        }
    }

    public function archiveOffer($id)
    {
        $user = auth()->user(); //get authenticated user

        if ($user->role !== 'admin') { //check if user is admin
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        else { //if user is admin
            $offerToArchive = Offer::find($id); //find offer by id

            if ($offerToArchive === null) { //if offer to archive is not found
                return response()->json([
                    'message' => 'No offer found'
                ], 404);
            } 
            else {
                $offerData = json_encode($offerToArchive->toArray()); //convert offer data to json
                Log::info('Offer to archive: ' . $offerData); //log offer data

                $directoryPath = public_path('archived_offers'); //path to save blocked offers

                if (!file_exists($directoryPath)) { //check if directory exists
                    mkdir($directoryPath, 0777, true); //create directory
                }

                file_put_contents($directoryPath . '/' . $offerToArchive->id . '.json', $offerData); //save offer data to file
                $offerToArchive->delete(); //delete offer

                return response()->json([
                    'message' => 'Offer archived successfully',
                    'offer' => $offerToArchive
                ], 200);
            }

            $offer->update(['status' => 'archived']); //update offer status to archived

            return response()->json([
                'message' => 'Offer archived successfully',
                'offer' => $offer
            ], 200);
        }
    }

    public function userOffers($id)
    {
        $user = auth()->user(); //get authenticated user
        // Check if the authenticated user is the same as the user whose offers are being fetched
        if ($user->id != $id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        else {
            $offers = Offer::where('user_id', $user->id)->get(); //get offers by user id
            if ($offers->isEmpty()) {
                return response()->json([
                    'message' => 'No offers found'
                ], 404);
            }  
            return response()->json([
                'message' => 'Offers fetched successfully',
                'offers' => $offers
            ], 200);
        }
    }

    //search
    public function searchByTitle($title)
    {
        $offers = Offer::where('title', 'like', '%' . $title . '%')->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  

        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }
    
    public function searchByDescription($description)
    {
        $offers = Offer::where('description', 'like', '%' . $description . '%')->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  
        
        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }
    
    public function searchByPrice($price)
    {
        $offers = Offer::where('price', 'like', '%' . $price . '%')->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  
        
        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }
    
    public function searchByCategory($category)
    {
        $offers = Offer::where('category', 'like', '%' . $category . '%')->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  

        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }
    
    public function searchByLocation($location)
    {
        $offers = Offer::where('location', 'like', '%' . $location . '%')->get(); //search by location
    
        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  
    
        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);
    }

    //filter
    public function filterByMinPrice($minPrice)
    {
        $offers = Offer::where('price', '>=', $minPrice)->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  

        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }
    
    public function filterByMaxPrice($maxPrice)
    {
        $offers = Offer::where('price', '<=', $maxPrice)->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        } 
        
        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);
 
    }
    
    public function filterByCategory($category)
    {
        $offers = Offer::where('category', 'like', '%' . $category . '%')->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }  
        
        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }
    
    public function filterByLocation($location)
    {
        $offers = Offer::where('location', 'like', '%' . $location . '%')->get();

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        } 
        
        return response()->json([
            'message' => 'Offer fetched successfully',
            'offers' => $offers
        ], 200);

    }

    public function categoryOffers($category)
    {
        $user = auth()->user(); //get authenticated user

        if ($user->role !== 'admin') { //check if user is admin
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        else { //if user is admin
            $offers = Offer::where('category', $category)->get(); //get offers by category

            if ($offers->isEmpty()) {
                return response()->json([
                    'message' => 'No offers found'
                ], 404);
            }  

            return response()->json([
                'message' => 'Offers fetched successfully',
                'offers' => $offers
            ], 200);
        }
    }

    public function locationOffers($location)
    {
        $user = auth()->user(); //get authenticated user

        if ($user->role !== 'admin') { //check if user is admin
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        else { //if user is admin
            $offers = Offer::where('location', $location)->get(); //get offers by location

            if ($offers->isEmpty()) {
                return response()->json([
                    'message' => 'No offers found'
                ], 404);
            }  

            return response()->json([
                'message' => 'Offers fetched successfully',
                'offers' => $offers
            ], 200);
        }
    }

    public function acceptOffer($id)
    {
        $admin = auth()->user(); //get authenticated user

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        else {
            $offer = Offer::find($id);

            if ($offer === null) {
                return response()->json([
                    'message' => 'No offer found'
                ], 404);
            }  

            $offer->update(['status' => 'accepted']);

            return response()->json([
                'message' => 'Offer accepted successfully',
                'offer' => $offer
            ], 200);
        }
    }

    public function denyOffer($id)
    {
        $admin = auth()->user(); //get authenticated user

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }
        else {
            $offer = Offer::find($id);

            if ($offer === null) {
                return response()->json([
                    'message' => 'No offer found'
                ], 404);
            }  

            $offer->update(['status' => 'inaccepted']);

            return response()->json([
                'message' => 'Offer denied successfully',
                'offer' => $offer
            ], 200);
        }
    }

    public function search(Request $request) //search offers by location, category, title, or description
    {
        $searchTerm = $request->input('searchTerm'); //get search term

        $offers = DB::table('offers')
            ->where('location', 'LIKE', "%{$searchTerm}%") //search by location
            ->orWhere('category', 'LIKE', "%{$searchTerm}%") //search by category
            ->orWhere('title', 'LIKE', "%{$searchTerm}%") //search by title
            ->orWhere('description', 'LIKE', "%{$searchTerm}%") //search by description
            ->orWhere('price', 'LIKE', "%{$searchTerm}%") //search by price
            ->get(); //get offers

        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }
        
        return response()->json([ //return response
            'message' => 'Offers fetched successfully',
            'offers' => $offers
        ], 200);
    }

}
