<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\ParentStudent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ParentsController extends Controller
{
    public function addNewParentToDB(Request $request) { //databasedeki Parent table ına yeni eleman ekler.
        if ($request->isMethod('post')) {
            if (session()->has('login_control')) {
                if (session('login_control') == 1) { // daha önce login girişi yapıldı mı kontrolü yapar
                    $parents = new Parents();
                    $parents->name = $request->name;    
                    $password = $request->username . "123"; // otomatik şifre ayarladım.
                    $parents->password = $password;
                    $parents->username = $request->username;
                    $parents->phone = $request->phone;

                    if (!(Parents::searchUserName($request->username))) { // daha önce bu username kullanılmış mı kullanılmamış mı diye kontrol ettim.
                        $parents->save();
                        $parents = Parents::getLastElement();
                        if (is_iterable($request->student_id)){
                            foreach($request->student_id as $student_id){ // parent_student table ına yeni veli ve öğrenci ekledim
                                $parent_student = new ParentStudent();
                                $parent_student->parent_id = $parents->parent_id;
                                $parent_student->student_id = $student_id;
                                $parent_student->save();
                            }
                        }
                        else{
                            $parent_student = new ParentStudent();
                            $parent_student->parent_id = $parents->parent_id;
                            $parent_student->student_id = $request->student_id;
                            $parent_student->save();
                        }
                    }
                    else{
                        $data["students"] = Student::getAllStudents();
                        $data["parents"] = Parents::getAllParents();
                        $data["error"] = "Bu username daha önce kullanıldı";
                        dd($data);
                        //return view("index", compact("data")); // !!!buraya yazılmış olan blade in adı girilecek şuan öylesine koydum
                    }
                }
                else {
                    return  view("index"); // giriş yapılmadıysa login ekranına yollanır
                }
            }
            return  view("index"); // Daha önce hiç login yapılmamışsa tarayıcı açıldığından beri direkt login sayfasına yönlendir
        }
        else{
            ParentsController::readParentsFromDB();
        }
    }

    //Öğretmenleri liste halinde blade e yollama fonksiyonu
    public function readParentsFromDB(){
        if (session()->has('login_control')) {
            if (session('login_control') == 1) { // daha önce login girişi yapıldı mı kontrolü yapar
                $data["students"] = Student::getAllStudents();
                $data["parents"] = Parents::getAllParents();
                dd($data);
                //return view("index", compact("data")); // !!!buraya yazılmış olan blade in adı girilecek şuan öylesine koydum
            } else {
                return  view("index"); // giriş yapılmadıysa login ekranına yollanır
            }
        }
        return  view("index"); // Daha önce hiç login yapılmamışsa tarayıcı açıldığından beri direkt login sayfasına yönlendir
    }

    public function updateParent(Request $request){
        $parents = Parents::getTeacher($request->parent_id);

        if ($parents->username != $request->username){ //Eğer güncelleme yaparken username değiştirilmişse
            if (!(Parents::searchUserName($request->username))) { // daha önce bu username kullanılmış mı kullanılmamış mı diye kontrol ettim.
                ParentStudent::deleteRowsByParentId($request->parent_id);

                $parents->name = $request->name;    
                $parents->username = $request->username;
                $parents->phone = $request->phone;
                $parents->save();

                foreach($request->student_id as $student_id){ // parent_student table ına yeni veli ve öğrenci ekledim
                    $parent_student = new ParentStudent();
                    $parent_student->parent_id = $parents->parent_id;
                    $parent_student->student_id = $student_id;
                    $parent_student->save();
                }
            }
            else{
                 // burada tekrar aynı ekleme sayfasına return yapıp o sayfada hata bastırtmalıyız. Bu username daha önce kullanıldı şeklinde
            }
        }
        else{
            ParentStudent::deleteRowsByParentId($request->parent_id);

            $parents->name = $request->name;    
            $parents->phone = $request->phone;
            $parents->save();
            
            foreach($request->student_id as $student_id){ // parent_student table ına yeni veli ve öğrenci ekledim
                $parent_student = new ParentStudent();
                $parent_student->parent_id = $parents->parent_id;
                $parent_student->student_id = $student_id;
                $parent_student->save();
            }
        }
    }

    public function deleteParent($parentId){
        Parents::deleteParentInId($parentId);
        ParentStudent::deleteRowsByParentId($parentId);
    }

    public function deneme($request) { //databasedeki Parent table ına yeni eleman ekler.
        $parents = new Parents();
        $parents->name = $request["name"];    
        $password = $request["username"] . "123"; // otomatik şifre ayarladım.
        $parents->password = $password;
        $parents->username = $request["username"];
        $parents->phone = $request["phone"];

        if (!(Parents::searchUserName($request["username"]))) { // daha önce bu username kullanılmış mı kullanılmamış mı diye kontrol ettim.
            $parents->save();
            $parents = Parents::getLastElement();
            foreach($request["student_id"] as $student_id){ // parent_student table ına yeni öğretmen ve sınıf ekledim
                $parent_student = new ParentStudent();
                $parent_student->parent_id = $parents->parent_id;
                $parent_student->student_id = $student_id;
                $parent_student->save();
            }
        }
    }

    public function deneme2() { //databasedeki Parent table ına yeni eleman ekler.
        $data["name"] = "murat";
        $data["username"] = "muratkaya";
        $data["phone"] = "12345";
        $data["student_id"][0] = 1;
        $data["student_id"][1] = 2;

        ParentsController::deneme($data);
    }

    public function example(){
        $parentId = 3; // Örnek olarak bir parent ID'si
        $parent = Parents::getParentWithStudent($parentId);
        dd($parent);
    }
}