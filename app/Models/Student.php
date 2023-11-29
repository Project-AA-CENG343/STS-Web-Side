<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 'student';
    protected $primaryKey = 'student_id';

    public static function getAllStudents(){
        return student::all();
    }

    public function classroom() {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    public static function getClassroomWithStudent($studentId){
        return student::with('classroom')->where("student_id", $studentId)->first();
    }

    public static function searchUserName($username){
        return student::where('username', $username)->exists();
    }

    public static function getLastElement(){
        return student::latest()->first();
    }
}