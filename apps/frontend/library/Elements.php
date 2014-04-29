<?

namespace Kladr\Frontend\Library {

use \Phalcon\Mvc\User\Component,
\Kladr\Frontend\Models\Users;

class Elements extends Component
{

public function getAuth()
{
$id = $this->session->get('user');        
$user = $id ? Users::findById($id) : null;

if($user){
return '<a href="/logout/" class="btr-reg sing-out">Выйти</a>'.
'<a href="" class="btr-reg recovery" style="margin-right: 10px;">Мои данные</a>'.
'<a href="/personal/" class="btn-enter user">Мои ключи</a>';
} else {
return '<a href="/register/" class="btr-reg sing-in">Создать аккаунт</a>'.
'<a href="/login/" class="btn-enter login">Войти</a>';
}
}

public function getTopMenu()
{
$result = '<ul>';        
foreach($this->config->menu as $key => $name){
$result .= '<li><a '.(preg_match('/^\/'.$key.'/', $this->request->getServer('REQUEST_URI')) ? 'class="selected" ' : '').'href="/'.$key.'/">'.$name.'</a></li>';
}        
$result .= '</ul>';
return $result;
}

public function getBottomMenu()
{
$result = '<ul>';        
foreach($this->config->menu as $key => $name){
$result .= '<li><a href="/'.$key.'/">'.$name.'</a></li>';
}        
$result .= '</ul>';
return $result;
}

}

}