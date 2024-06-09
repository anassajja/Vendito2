<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\User;

class ContactController extends Controller
{
    public function index()
    {
        $user = auth()->user(); //get authenticated user

       if ($user->is_admin) {
            $contacts = Contact::all();
            if ($contacts->isEmpty()) {
                return response()->json([
                    'message' => 'No contact message found'
                ], 404);
            }
            else {
                return response()->json([
                    'message' => 'Contacts retrieved successfully',
                    'contacts' => $contacts
                ], 200);
            }
            
        } else {
            return response()->json([
                'message' => 'You are not authorized to view this page'
            ], 403);
        }
    }

    public function show($id)
    {
        $contact = Contact::find($id);

        if ($contact === null) {
            return response()->json([
                'message' => 'No contact message found'
            ], 404);
        }  

        return response()->json([
            'message' => 'Contact retrieved successfully',
            'contact' => $contact
        ], 200);
    }

    public function delete($id)
    {
        $admin = auth()->user(); //get authenticated user

        if ($admin->is_admin) {
            $contact = Contact::find($id);

            if ($contact === null) {
                return response()->json([
                    'message' => 'No contact message found'
                ], 404);
            }  

            $contact->delete();

            return response()->json([
                'message' => 'Contact deleted successfully'
            ], 200);
        } else {
            return response()->json([
                'message' => 'You are not authorized to delete this contact message'
            ], 403);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required', 
            'subject' => 'required',
            'message' => 'required',
        ]);
        $contact = Contact::create($request->all());

        if ($contact === null) {
            return response()->json([
                'message' => 'Contact not created'
            ], 404);
        }
        else  {
            return response()->json([
                'message' => 'Contact created successfully',
                'contact' => $contact
            ], 201);
        }

    }

    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);

        if ($contact === null) {
            return response()->json([
                'message' => 'No contact message found'
            ], 404);
        }  

        $contact->update($request->all());

        return response()->json([
            'message' => 'Contact updated successfully',
            'contact' => $contact
        ], 200);
    }
}
