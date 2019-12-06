<?php

require_once 'forum_grade_grades_getter.php';

use need_to_check_lib as nlib;

abstract class GradeGradesGetter
{
    public function get_grades()
    {
        $grades = $this->get_ungraded_users();
        $grades = $this->filter_out_all_non_student_grades($grades);

        /* Форум отключен из-за того, что работает не так как преполагалось !!!
        $forumGrades = $this->get_forum_grades();
        if(is_array($forumGrades))
        {
            $grades = array_merge($grades, $forumGrades);
            usort($grades, "cmp_need_to_check_courses");
        }
        */

        return $grades;
    }

    protected function get_ungraded_users()
    {
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                        gg.userid, gi.courseid, c.fullname AS coursename
                FROM {grade_grades} AS gg
                INNER JOIN {grade_items} AS gi
                ON gg.itemid=gi.id
                INNER JOIN {user} AS u
                ON gg.userid = u.id
                INNER JOIN {course} AS c
                ON gi.courseid = c.id
                WHERE gg.finalgrade IS NULL
                AND gi.itemmodule IN ('assign', 'quiz') 
                AND gi.hidden=0 
                AND u.suspended=0
                AND u.deleted=0
                ORDER BY c.shortname, gi.itemname"; 

        global $DB;
        $grades = $DB->get_records_sql($sql, array());
        $grades = $this->filter_grades($grades);
        return $grades;
    }

    abstract protected function filter_grades($grades);

    private function filter_out_all_non_student_grades(array $grades)
    {
        $studentArchetypes = nlib\get_archetypes_roles(array('student'));

        foreach($grades as $key => $grade)
        {
            $cm = nlib\get_course_module($grade);
            $userRoles = get_user_roles(\context_module::instance($cm->id), $grade->userid);

            if($this->is_user_not_student($userRoles, $studentArchetypes))
            {
                unset($grades[$key]);
            }
        }

        return $grades;
    }

    private function is_user_not_student(array $userroles, array $studentarchetypes) : bool 
    {
        foreach($userroles as $userrole)
        {
            foreach($studentarchetypes as $studentarchetype)
            {
                if($userrole->roleid == $studentarchetype->id) return false;
            }
        }
    
        return true;
    }

    abstract protected function get_forum_grades();
}

function cmp_need_to_check_courses($a, $b)
{
    return strcmp($a->coursename, $b->coursename);
}

/**
 * Returns grades for user with global role based on manager archetype.
 */
class GlobalManagerGradeGradesGetter extends GradeGradesGetter
{
    protected function filter_grades($grades)
    {
        return $grades;
    }

    protected function get_forum_grades()
    {
        $forum = new GlobalManagerForumUnratedPostsGetter();
        return $forum->get_grades();
    }
}

/**
 * Returns grades for user with role based on any archetype in the courses.
 */
abstract class LocalGradeGradesGetter extends GradeGradesGetter 
{
    protected $archetypeRoles;

    protected function get_ungraded_users()
    {
        $sql = "SELECT gg.id, gg.itemid, gi.itemname, gi.itemmodule, gi.iteminstance, 
                        gg.userid, gi.courseid, c.fullname AS coursename
                FROM {grade_grades} AS gg
                INNER JOIN {grade_items} AS gi
                ON gg.itemid=gi.id
                INNER JOIN {user} AS u
                ON gg.userid = u.id
                INNER JOIN {course} AS c
                ON gi.courseid = c.id
                WHERE gg.finalgrade IS NULL
                AND gi.itemmodule IN ('assign', 'quiz') 
                AND gi.hidden=0 ";

        $userCoursesCondition = $this->get_user_courses_condition();
        if(!empty($userCoursesCondition))
        {
            $sql.= $userCoursesCondition;
        }

        $sql.= "AND u.suspended=0
                AND u.deleted=0
                ORDER BY c.shortname, gi.itemname"; 

        global $DB;
        $grades = $DB->get_records_sql($sql, array());
        $grades = $this->filter_grades($grades);
        return $grades;
    }

    private function get_user_courses_condition()
    {
        global $USER;
        $userCourses = nlib\get_user_courses($USER->id);

        if(count($userCourses)) $cond = 'AND gi.courseid IN (';
        else return '';

        foreach($userCourses as $course)
        {
            $cond.= $course;

            if(end($userCourses) != $course) $cond.= ', ';
            else $cond.= ') ';
        }

        return $cond;
    }

    protected function filter_grades($grades)
    {
        return $this->filter_out_all_non_user_grades($grades);
    }

    private function filter_out_all_non_user_grades($grades)
    {
        global $USER;

        $userGrades = array();
        foreach($grades as $grade)
        {
            $cmid = nlib\get_course_module_id($grade);
            $userRoles = get_user_roles(\context_module::instance($cmid), $USER->id);

            if(nlib\is_user_have_role($this->archetypeRoles, $userRoles))
            {
                $userGrades[] = $grade;
            }

        }
        return $userGrades;
    }

    protected function get_forum_grades()
    {
        
    }
}

/**
 * Returns grades for user with role based on manager archetype in the courses.
 */
class LocalManagerGradeGradesGetter extends LocalGradeGradesGetter
{
    function __construct() 
    {
        $this->archetypeRoles = nlib\get_archetypes_roles(array('manager'));
    }

    protected function get_forum_grades()
    {
        $forum = new LocalManagerForumUnratedPostsGetter();
        return $forum->get_grades();
    }
}

/**
 * Returns grades for user with role based on teacher or editingteacher archetypes in the courses.
 */
class LocalTeacherGradeGradesGetter extends LocalGradeGradesGetter
{
    function __construct() 
    {
        $this->archetypeRoles = nlib\get_archetypes_roles(array('teacher', 'editingteacher'));
    }

    protected function get_forum_grades()
    {
        $forum = new LocalTeacherForumUnratedPostsGetter();
        return $forum->get_grades();
    }
}
