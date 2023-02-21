<?php

namespace File;

class File
{
    public function __construct(private $filePath)
    {
    }
    
    public function save(array $newUser)
    {
        $users = json_decode(file_get_contents($this->filePath), true) ?? [];

//        $newUsers['users'] = array_merge([$users['users']], $newUser);
        $users['users'][] = $newUser;
        file_put_contents($this->filePath, json_encode($users));
    }

    public function read()
    {

    }
}