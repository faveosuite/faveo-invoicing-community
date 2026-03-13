<?php

namespace App\Model\Github;

use App\BaseModel;

class GithubRepo extends BaseModel
{
    protected $table = 'github_repos';

    protected $fillable = ['display_name', 'owner', 'repo', 'workflow_file', 'dispatch_branch'];
}
