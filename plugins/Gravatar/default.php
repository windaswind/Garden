<?php if (!defined('APPLICATION')) exit();

// Define the plugin:
$PluginInfo['Gravatar'] = array(
   'Name' => 'Gravatar',
   'Description' => 'Implements Gravatar avatars for all users who have not uploaded their own custom profile picture & icon.',
   'Version' => '1',
   'Author' => "Mark O'Sullivan",
   'AuthorEmail' => 'mark@vanillaforums.com',
   'AuthorUrl' => 'http://vanillaforums.com'
);

class GravatarPlugin implements Gdn_IPlugin {
   
   // Find all the places where UserBuilder is called, and make sure that there
   // is a related $UserPrefix.'Email' field pulled from the database.
   public function Gdn_AddonCommentModel_BeforeGet_Handler(&$Sender) {
      $Sender->SQL->Select('iu.Email', '', 'InsertEmail');
   }
   public function Gdn_ConversationModel_BeforeGet_Handler(&$Sender) {
      $Sender->SQL->Select('lmu.Email', '', 'LastMessageEmail');
   }
   public function Gdn_ConversationMessageModel_BeforeGet_Handler(&$Sender) {
      $Sender->SQL->Select('iu.Email', '', 'InsertEmail');
   }
   public function Gdn_ActivityModel_BeforeGet_Handler(&$Sender) {
      $Sender->SQL
         ->Select('au.Email', '', 'ActivityEmail')
         ->Select('ru.Email', '', 'RegardingEmail');
   }
   public function Gdn_ActivityModel_BeforeGetComments_Handler(&$Sender) {
      $Sender->SQL->Select('au.Email', '', 'ActivityEmail');
   }
   public function Gdn_UserModel_BeforeGetActiveUsers_Handler(&$Sender) {
      $Sender->SQL->Select('u.Email');
   }
   public function Gdn_CommentModel_BeforeGet_Handler(&$Sender) {
      $Sender->SQL->Select('iu.Email', '', 'InsertEmail');
   }

   public function Gdn_CommentModel_BeforeGetNew_Handler(&$Sender) {
      $Sender->SQL->Select('iu.Email', '', 'InsertEmail');
   }

   public function Setup() {
      // No setup required.
   }
}

if (!function_exists('UserBuilder')) {
   /**
    * Override the default UserBuilder function with one that switches the photo
    * out with a gravatar url if the photo is empty.
    */
   function UserBuilder($Object, $UserPrefix = '') {
      $User = new stdClass();
      $UserID = $UserPrefix.'UserID';
      $Name = $UserPrefix.'Name';
      $Photo = $UserPrefix.'Photo';
      $Email = $UserPrefix.'Email';
      $User->UserID = $Object->$UserID;
      $User->Name = $Object->$Name;
      $User->Photo = property_exists($Object, $Photo) ? $Object->$Photo : '';
      if ($User->Photo == '' && property_exists($Object, $Email)) {
         $User->Photo = 'http://www.gravatar.com/avatar.php?'
            .'gravatar_id='.md5(strtolower($Object->$Email))
            .'&default='.urlencode(Url(Gdn::Config('Plugins.Gravatar.DefaultAvatar', 'plugins/Gravatar/default.gif'), TRUE))
            .'&size='.Gdn::Config('Garden.Thumbnail.Width', 40);
      }
		return $User;
   }
}