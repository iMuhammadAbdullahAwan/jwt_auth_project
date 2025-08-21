<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Validation\StrictRules\CreditCardRules;
use CodeIgniter\Validation\StrictRules\FileRules;
use CodeIgniter\Validation\StrictRules\FormatRules;
use CodeIgniter\Validation\StrictRules\Rules;

class Validation extends BaseConfig
{
    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var list<string>
     */
    public array $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public array $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------

    /**
     * User validation rules
     *
     * @var array<string, string>
     */
    public array $user = [
        'first_name'     => 'required|min_length[2]|max_length[100]',
        'last_name'      => 'required|min_length[2]|max_length[100]',
        'email'          => 'required|valid_email',
        'password_hash'  => 'required|min_length[8]',
        'role_id'        => 'required|integer|is_not_unique[user_roles.id]'
    ];

    public array $login = [
        'email'          => 'required|valid_email',
        'password_hash'  => 'required'
    ];

    public array $register = [
        'first_name'     => 'required|min_length[2]|max_length[100]',
        'last_name'      => 'required|min_length[2]|max_length[100]',
        'email'          => 'required|valid_email|is_unique[users.email]',
        'password_hash'  => 'required|min_length[8]',
        'role_id'        => 'required|integer|is_not_unique[user_roles.id]'
    ];

    public array $forgotPassword = [
        'email' => 'required|valid_email'
    ];

    public array $resetPassword = [
        'token'    => 'required',
        'password' => 'required|min_length[8]'
    ];

    public array $refreshToken = [
        'refresh_token' => 'required'
    ];
}
