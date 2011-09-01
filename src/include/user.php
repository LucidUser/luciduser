<?php
/*
	UserCake Version: 1.4
	http://usercake.com
	
	Developed by: Adam Davis
*/

class User 
{
	public $user_active = 0;
	private $clean_email;
	public $status = false;
	private $clean_password;
	private $clean_username;
	private $unclean_username;
	public $sql_failure = false;
	public $mail_failure = false;
	public $email_taken = false;
	public $username_taken = false;
	public $activation_token = 0;
	
	function __construct($user,$pass,$email)
	{
		//Used for display only
		$this->unclean_username = $user;
		
		//Sanitize
		$this->clean_email = sanitize($email);
		$this->clean_password = trim($pass);
		$this->clean_username = sanitize($user);
		
		if(usernameExists($this->clean_username))
		{
			$this->username_taken = true;
		}
		else if(emailExists($this->clean_email))
		{
			$this->email_taken = true;
		}
		else
		{
			//No problems have been found.
			$this->status = true;
		}
	}
	
	public function userCakeAddUser()
	{
		global $db,$emailActivation,$websiteUrl,$db_table_prefix;
	
		//Prevent this function being called if there were construction errors
		if($this->status)
		{
			//Construct a secure hash for the plain text password
			$secure_pass = generateHash($this->clean_password);
			
			//Construct a unique activation token
			$this->activation_token = generateActivationToken();
	
			//Do we need to send out an activation email?
			if($emailActivation)
			{
				//User must activate their account first
				$this->user_active = 0;
			
				$mail = new userCakeMail();
			
				//Build the activation message
				$activation_message = lang("ACTIVATION_MESSAGE",array($websiteUrl,$this->activation_token));
				
				//Define more if you want to build larger structures
				$hooks = array(
					"searchStrs" => array("#ACTIVATION-MESSAGE","#ACTIVATION-KEY","#USERNAME#"),
					"subjectStrs" => array($activation_message,$this->activation_token,$this->unclean_username)
				);
				
				/* Build the template - Optional, you can just use the sendMail function 
				Instead to pass a message. */
				if(!$mail->newTemplateMsg("new-registration.txt",$hooks))
				{
					$this->mail_failure = true;
				}
				else
				{
					//Send the mail. Specify users email here and subject. 
					//SendMail can have a third parementer for message if you do not wish to build a template.
					
					if(!$mail->sendMail($this->clean_email,"New User"))
					{
						$this->mail_failure = true;
					}
				}
			}
			else
			{
				//Instant account activation
				$this->user_active = 1;
			}	
	
	
			if(!$this->mail_failure)
			{
					//Insert the user into the database providing no errors have been found.
					$sql = "INSERT INTO `".$db_table_prefix."Users` (
							`Username`,
							`Username_Clean`,
							`Password`,
							`Email`,
							`ActivationToken`,
							`LastActivationRequest`,
							`LostPasswordRequest`, 
							`Active`,
							`Group_ID`,
							`SignUpDate`,
							`LastSignIn`
							)
					 		VALUES (
							'".$db->sql_escape($this->unclean_username)."',
							'".$db->sql_escape($this->clean_username)."',
							'".$secure_pass."',
							'".$db->sql_escape($this->clean_email)."',
							'".$this->activation_token."',
							'".time()."',
							'0',
							'".$this->user_active."',
							'1',
							'".time()."',
							'0'
							)";
					 
				return $db->sql_query($sql);
			}
		}
	}
}

class loggedInUser {

	public $email = NULL;
	public $hash_pw = NULL;
	public $user_id = NULL;
	public $clean_username = NULL;
	public $display_username = NULL;
	
	//Simple function to update the last sign in of a user
	public function updateLastSignIn()
	{
		global $db,$db_table_prefix;
		
		$sql = "UPDATE ".$db_table_prefix."Users
			    SET
				LastSignIn = '".time()."'
				WHERE
				User_ID = '".$db->sql_escape($this->user_id)."'";
		
		return ($db->sql_query($sql));
	}
	
	//Return the timestamp when the user registered
	public function signupTimeStamp()
	{
		global $db,$db_table_prefix;
		
		$sql = "SELECT
				SignUpDate
				FROM
				".$db_table_prefix."Users
				WHERE
				User_ID = '".$db->sql_escape($this->user_id)."'";
		
		$result = $db->sql_query($sql);
		
		$row = $db->sql_fetchrow($result);
		
		return ($row['SignUpDate']);
	}
	
	//Update a users password
	public function updatePassword($pass)
	{
		global $db,$db_table_prefix;
		
		$secure_pass = generateHash($pass);
		
		$this->hash_pw = $secure_pass;
		
		$sql = "UPDATE ".$db_table_prefix."Users
		       SET
			   Password = '".$db->sql_escape($secure_pass)."' 
			   WHERE
			   User_ID = '".$db->sql_escape($this->user_id)."'";
	
		return ($db->sql_query($sql));
	}
	
	//Update a users email
	public function updateEmail($email)
	{
		global $db,$db_table_prefix;
		
		$this->email = $email;
		
		$sql = "UPDATE ".$db_table_prefix."Users
				SET Email = '".$email."'
				WHERE
				User_ID = '".$db->sql_escape($this->user_id)."'";
		
		return ($db->sql_query($sql));
	}
	
	//Fetch all user group information
	public function groupID()
	{
		global $db,$db_table_prefix;
		
		$sql = "SELECT ".$db_table_prefix."Users.Group_ID, 
			   ".$db_table_prefix."Groups.* 
			   FROM ".$db_table_prefix."Users
			   INNER JOIN ".$db_table_prefix."Groups ON ".$db_table_prefix."Users.Group_ID = ".$db_table_prefix."Groups.Group_ID 
			   WHERE
			   User_ID  = '".$db->sql_escape($this->user_id)."'";
		
		$result = $db->sql_query($sql);
		
		$row = $db->sql_fetchrow($result);

		return($row);
	}
	
	//Is a user member of a group
	public function isGroupMember($id)
	{
		global $db,$db_table_prefix;
	
		$sql = "SELECT ".$db_table_prefix."Users.Group_ID, 
				".$db_table_prefix."Groups.* FROM ".$db_table_prefix."Users 
				INNER JOIN ".$db_table_prefix."Groups ON ".$db_table_prefix."Users.Group_ID = ".$db_table_prefix."Groups.Group_ID
				WHERE User_ID  = '".$db->sql_escape($this->user_id)."'
				AND
				".$db_table_prefix."Users.Group_ID = '".$db->sql_escape($db->sql_escape($id))."'
				LIMIT 1
				";
		
		if(returns_result($sql))
			return true;
		else
			return false;
		
	}
	
	//Logout
	function userLogOut()
	{
		destorySession("userCakeUser");
	}

}