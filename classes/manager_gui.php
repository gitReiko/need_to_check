<?php

class NeedToCheckManagerGUI 
{
    private $courses;

    function __construct($grades)
    {
        $data = new GuiDataGetter($grades);
        $this->courses = $data->get_manager_courses_array();
    }

    public function get_gui() : string 
    {
        $gui = '';

        if(is_siteadmin()) $gui.= $this->get_update_button();
        
        if(count($this->courses))
        {
            $gui.= '<h6><u>'.get_string('works_for_teachers', 'block_need_to_check').':</u></h6>';
        }

        foreach($this->courses as $course)
        {
            $courseid = $this->get_courseid($course);
            $gui.= $this->get_course_row($course, $courseid);
            $gui.= $this->get_course_container_begin($courseid);

            foreach($course->get_teachers() as $teacher)
            {
                $teacherid = $this->get_teacherid($course, $teacher);
                $gui.= $this->get_teacher_row($teacher, $teacherid);
                $gui.= $this->get_items_container_begin($teacherid);

                foreach($teacher->get_items() as $item)
                {
                    $gui.= $this->get_item_row($item);
                }

                $gui.= $this->get_container_end();
            }

            $gui.= $this->get_container_end();
        }

        return $gui;
    }

    private function get_update_button() : string 
    {
        $btn = '<form>';
        $btn.= '<input type="submit" 
                       value="'.get_string('update_teacher_table', 'block_need_to_check').'" 
                       name="update"
                       title="'.get_string('update_teacher_table_title', 'block_need_to_check').'" />';
        $btn.= '<input type="hidden" name="ntc_update_teacher_table" value="1"/>';
        $btn.= '</form><br>';

        return $btn;
    }

    private function get_course_row($course, $courseid) : string 
    {
        $row = '<div class="chekingTeacher horizontal-node" 
                        id="course'.$courseid.'" 
                        onclick="hide_or_show_block(`'.$courseid.'`, `course`)">';
        //$row.= '<a href="'.$course->get_link().'" target="_blank">';
        $row.= $course->get_name();
        $row.= $this->get_unchecked_and_expired_string($course);
        $row.= ' <a href="'.$course->get_link().'" target="_blank">';
        $row.= '➢ '.get_string('go_to_course', 'block_need_to_check').'</a>';
        $row.= '</div>';
        return $row;
    }

    private function get_teacher_row($teacher, $teacherid) : string 
    {
        $row = '<div class="chekingTeacher horizontal-node jqueryTooltip" 
                        id="teacher'.$teacherid.'" 
                        onclick="hide_or_show_block(`'.$teacherid.'`, `teacher`)" 
                        style="margin-left: 20px !important;" ';
        if(!empty($teacher->get_contacts())) $row.= 'title="'.$teacher->get_contacts().'" ';
        $row.= '> ';

        if(!empty($teacher->get_name())) $row.= $teacher->get_name();
        else $row .= get_string('not_assigned', 'block_need_to_check');

        $row.= $this->get_unchecked_and_expired_string($teacher);
        $row.= '</div>';
        return $row;
    }

    private function get_item_row($item) : string 
    {
        $row = '<a href="'.$item->get_link().'" target="_blank">';
        $row.= '<div class="chekingItem">';
        $row.= $item->get_name();
        $row.= $this->get_unchecked_and_expired_string($item);
        $row.= '</div>';
        $row.= '</a>';
        return $row;
    }

    private function get_unchecked_and_expired_string($value) : string 
    {
        // (xx - xx) - xx - works count.
        $str = ' ('.$value->get_unchecked_works_count().' - ';
        $str.= '<span style="color: red;">'.$value->get_expired_works_count().'</span>)';
        return $str;
    }

    private function get_items_container_begin($teacherid) : string 
    {
        return "<div id='{$teacherid}' style='display: none;'>";
    }

    private function get_container_end() : string 
    {
        return '</div>';
    }

    private function get_teacherid($course, $teacher) : string 
    {
        return "{$course->get_id()}+{$teacher->get_id()}";
    }

    private function get_courseid($course)
    {
        return "{$course->get_id()}";
    }

    private function get_course_container_begin($courseid) : string 
    {
        return "<div id='{$courseid}' style='display: none;'>";
    }

}
