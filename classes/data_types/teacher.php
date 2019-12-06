<?php

use need_to_check_lib as nlib;

class CheckingTeacher extends ParentType
{

    // params neccessary for gui
    private $contacts;
    private $items;
    private $fullname;

    function __construct(stdClass $grade, $teacherid)
    {
        $this->id = $teacherid;
        $this->init_teacher_name_and_fullname();
        $this->contacts = $this->get_teacher_contacts();

        $this->uncheckedWorksCount = 0;
        $this->expiredWorksCount = 0;

        $this->items = array();
    }

    public function get_contacts() 
    {
        return $this->contacts;
    }

    public function get_items() : array
    {
        return $this->items;
    }

    public function add_item(CheckingItem $item) : void
    {
        $this->items[] = $item;
    }

    public function set_items(array $items)
    {
        $this->items = $items;
    }


    private function get_teacher_contacts()
    {
        $contacts = '';

        if(!empty($this->id))
        {
            global $DB;
            $teacher = $DB->get_record('user', array('id'=>$this->id), 'email, phone1, phone2');
            $newline = '<br>';

            $contacts.= $this->fullname.$newline;
            if(!empty($teacher->email))
            {
                $contacts.= '✉ '.$teacher->email.$newline;
            }
            if(!empty($teacher->phone1))
            {
                $contacts.= '☎ '.$teacher->phone1.$newline;
            }
            if(!empty($teacher->phone2))
            {
                $contacts.= '☎ '.$teacher->phone2;
            }
        }
        else 
        {
            $contacts = get_string('not_assigned', 'block_need_to_check');
        }

        return $contacts;
    }

    private function init_teacher_name_and_fullname() : void 
    {
        if(empty($this->id))
        {
            $this->name = '';
            $this->fullname = '';
        } 
        else
        {
            $user = $this->get_user_from_database($this->id);
            $this->name = $this->get_teacher_shortname($user);
            $this->fullname = $this->get_teacher_fullname($user);
        }
    }

    private function get_user_from_database(int $id) : stdClass
    {
        global $DB;
        return $DB->get_record('user', array('id'=>$id), 'id, firstname, lastname');
    }

    private function get_teacher_shortname(stdClass $teacher) : string 
    {
        $temp = explode(' ', $teacher->firstname);
        $str = ' ';

        foreach($temp as $key2 => $name)
        {
            $str .= mb_substr($name, 0, 1).'.';
        }

        return $teacher->lastname.$str;
    }

    private function get_teacher_fullname(stdClass $teacher) : string 
    {
        return $teacher->lastname.' '.$teacher->firstname;
    }

}
