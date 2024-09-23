<?php

namespace App\Services;

use App\Models\UserComplaint;
use InvalidArgumentException;

class UserComplaintService
{
    public function getAllUserComplaints()
    {
        $user_complaints = UserComplaint::paginate(10);

        if (!$user_complaints) {
            throw new InvalidArgumentException('There Is No User Complaints Available');
        }

        return $user_complaints;
    }

    public function getUserComplaint($user_complaint_id) : UserComplaint
    {
        $user_complaint = UserComplaint::find($user_complaint_id);
       
        if (!$user_complaint) {
            throw new InvalidArgumentException('User Complaint not found');
        }

        return $user_complaint;
    }
    public function getUserComplaintsByUser($user_id)
    {
        $user_complaints = UserComplaint::where('user_id', $user_id)->paginate(10);
        if (!$user_complaints) {
            throw new InvalidArgumentException('User Have no User Complaints');
        }

        return $user_complaints;
    }

    public function createUserComplaint(array $data, $user_id): UserComplaint
    {

        $user_complaint = UserComplaint::create([
            'user_id' => $user_id,
            'title' => $data['title'],
            'details' => $data['details'],
        ]);
        if(!$user_complaint){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $user_complaint;
    }

    public function updateUserComplaint(array $data, $user_complaint_id): UserComplaint
    {
        $user_complaint = UserComplaint::find($user_complaint_id);
        if(!$user_complaint){
            throw new InvalidArgumentException('There Is No User Complaints Available');
        }
        $user_complaint->update([
            'title' => $data['title'],
            'details' => $data['details'],
        ]);

        return $user_complaint;
    }

    public function show($user_complaint_id): UserComplaint
    {
        $user_complaint = UserComplaint::find($user_complaint_id);

        if(!$user_complaint){
            throw new InvalidArgumentException('User Complaint not found');
        }

        return $user_complaint;
    }

    public function delete(int $user_complaint_id) : void
    {
        $user_complaint = UserComplaint::find($user_complaint_id);

        if (!$user_complaint) {
            throw new InvalidArgumentException('User Complaint not found');
        }

        $user_complaint->delete();
    }

    public function forceDelete(int $user_complaint_id) : void
    {
        $user_complaint = UserComplaint::find($user_complaint_id);

        if (!$user_complaint) {
            throw new InvalidArgumentException('User Complaint not found');
        }

        $user_complaint->forceDelete();
    }
}
