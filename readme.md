# User

Access User data in Session、Cookie、Cache

## Installing

### install
```
composer required noking50/user
```

### config
```
'group' => [
    // frontend user
    'member' => [
        'login' => 'Put Route name of login',
        'logout' => 'Put Route name of login',
    ],

    // backend user
    'admin' => [
        'login' => 'Put Route name of login',
        'logout' => 'Put Route name of login',
        'super' => [... Super admin User data save in session  ...],
    ],

    ... Other group ...
],
```

## Usage

get User current group
```
User::group()
```

switch User group
```
User::group($group)
```

check user is login
```
User::group()
```

get user session data
```
User::get($key)
```

set user session data
```
User::set($key, $value)
```

delete user session data
```
User::forget($key)
```

