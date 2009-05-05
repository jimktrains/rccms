<?php

/**
 * Examples
 *
 * Some usage examples on DataMapper.
 *
 * @licence 	MIT Licence
 * @category	Models
 * @author  	Simon Stenhouse
 * @link    	http://stensi.com
 */
class Examples extends Controller {

	/**
	 * Constructor
	 *
	 * Initialize Examples.
	 */
	function Examples()
	{
		parent::Controller();

		$this->load->helper('url');
		$this->load->Model('User');

		$this->load->Model('Employee');
		$this->load->Model('Group');
		$this->load->Model('Manager');
		$this->load->Model('Supervisor');
		$this->load->Model('Underling');
		$this->output->enable_profiler(TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Index
	 */
	function index()
	{
		echo '<h1>DataMapper Example</h1>';

		echo '<h2>Create</h2>';
		echo '<p><a href="' . site_url('examples/create_users') . '">Create Users</a></p>';
		echo '<p><a href="' . site_url('examples/create_groups') . '">Create Groups</a></p>';

		echo '<h2>Update</h2>';
		echo '<p><a href="' . site_url('examples/update_user') . '">Update User</a></p>';

		echo '<h2>Relationships</h2>';
		echo '<p><a href="' . site_url('examples/add_relationships') . '">Add Relationships</a></p>';
		echo '<p><a href="' . site_url('examples/remove_relationships') . '">Remove Relationships</a></p>';

		echo '<h2>Delete</h2>';
		echo '<p><a href="' . site_url('examples/delete_user') . '">Delete User</a></p>';

		echo '<h2>Self Referencing Relationships</h2>';
		echo '<p><a href="' . site_url('examples/self_referencing') . '">Managers, Supervisors, and Underlings (all Employees)</a></p>';
	}

	// --------------------------------------------------------------------

	/**
	 * Create Users
	 */
	function create_users()
	{
		echo '<h1>Create Users</h1>';
		
		echo '<p>Here we create a new User object.</p>';
		echo '<code>
		// Create User<br />
		$u = new User();<br />
		$u->username = "Fred Smith";<br />
		$u->email = "fred@smith.com";</br />
		$u->password = "apples";<br />
		$u->confirm_password = "apples";</code>';

		// Create User
		$u = new User();
		$u->username = 'Fred Smith';
		$u->email = 'fred@smith.com';
		$u->password = 'apples';
		$u->confirm_password = 'apples';

		echo '<br />';
		echo '<p>Current values:</p>';
		echo '<code><strong>ID</strong>: ' . $u->id . '<br />' .
		'<strong>Username</strong>: ' . $u->username . '<br />' .
		'<strong>Email</strong>: ' . $u->email . '<br />' .
		'<strong>Password</strong>: ' . $u->password . '<br />' .
		'<strong>Confirm Password</strong>: ' . $u->confirm_password . '<br />' .
		'<strong>Salt</strong>: ' . $u->salt . '<br />' .
		'<strong>Created</strong>: ' . $u->created . '<br />' .
		'<strong>Updated</strong>: ' . $u->updated . '</code>';
		
		echo '<hr />';		
		echo '<p>Now to save it to the database.</p>';
		
		echo '<code>
		// Save User<br />
		$u->save();</code>';
		
		// Save User
		if ($u->save())
		{
			echo '<br />';
			echo '<p>Current values:</p>';
			
			echo '<code><strong>ID</strong>: ' . $u->id . '<br />' .
			'<strong>Username</strong>: ' . $u->username . '<br />' .
			'<strong>Email</strong>: ' . $u->email . '<br />' .
			'<strong>Password</strong>: ' . $u->password . '<br />' .
			'<strong>Confirm Password</strong>: ' . $u->confirm_password . '<br />' .
			'<strong>Salt</strong>: ' . $u->salt . '<br />';
			'<strong>Created</strong>: ' . $u->created . '<br />';
			'<strong>Updated</strong>: ' . $u->updated . '</code>';
		}
		else
		{
			echo '<p><b>User has already been created</b></p>';
		}
		
		echo '<hr />';
		echo '<p>Create a couple more users.</p>';

		echo '<code>
		$u = new User();<br />
		$u->username = "Jayne Doe";<br />
		$u->email = "jayne@doe.com";<br />
		$u->password = "poppies";<br />
		$u->confirm_password = "poppies";<br />
		$u->save();<br />
		<br />
		$u = new User();<br />
		$u->username = "Joe Public";<br />
		$u->email = "joe@public.com";<br />
		$u->password = "rockets";<br />
		$u->confirm_password = "rockets";<br />
		$u->save();</code>';

		$u = new User();
		$u->username = 'Jayne Doe';
		$u->email = 'jayne@doe.com';
		$u->password = 'poppies';
		$u->confirm_password = 'poppies';
		$u->save();
		
		$u = new User();
		$u->username = 'Joe Public';
		$u->email = 'joe@public.com';
		$u->password = 'rockets';
		$u->confirm_password = 'rockets';
		$u->save();
		
		echo '<hr />';
		echo '<p>Get all users and show them.</p>';
		
		echo '<code>// Get all users<br />
		$u = new User();<br />
		$u->get();<br />
		<br />
		foreach ($u->all as $user)<br />
		{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;echo "&lt;code&gt;<strong>ID</strong>: " . $user->id . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Username</strong>: " . $user->username . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Email</strong>: " . $user->email . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Password</strong>: " . $user->password . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Confirm Password</strong>: " . $user->confirm_password . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Salt</strong>: " . $user->salt . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Created</strong>: " . $user->created . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Updated</strong>: " . $user->updated . "&lt;/code&gt;&lt;br /&gt;&lt;br /&gt;";<br />
		}</code>';
		
		echo '<br />';
		echo '<p>This produces:</p>';

		// Get all users
		$u = new User();
		$u->get();
			
		foreach ($u->all as $user)
		{
			echo '<code><strong>ID</strong>: ' . $user->id . '<br />' .
				'<strong>Username</strong>: ' . $user->username . '<br />' .
				'<strong>Email</strong>: ' . $user->email . '<br />' .
				'<strong>Password</strong>: ' . $user->password . '<br />' .
				'<strong>Confirm Password</strong>: ' . $user->confirm_password . '<br />' .
				'<strong>Salt</strong>: ' . $user->salt . '<br />' .
				'<strong>Created</strong>: ' . $user->created . '<br />' .
				'<strong>Updated</strong>: ' . $user->updated . '</code><br /><br />';
		}
		
		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Create Groups
	 */
	function create_groups()
	{
		echo '<h1>Create Groups</h1>';
		
		echo '<p>Here we create a new Group object.</p>';
		echo '<code>
		// Create Group<br />
		$g = new Group();<br />
		$g->name = "Administrator";</code>';

		// Create Group
		$g = new Group();
		$g->name = 'Administrator';

		echo '<br />';
		echo '<p>Current values:</p>';
		echo '<code><strong>ID</strong>: ' . $g->id . '<br />' .
		'<strong>Name</strong>: ' . $g->name . '</code>';
		
		echo '<hr />';		
		echo '<p>Now to save it to the database.</p>';
		
		echo '<code>
		// Save User<br />
		$g->save();</code>';
		
		// Save Group
		if ($g->save())
		{
			echo '<br />';
			echo '<p>Current values:</p>';
			
			echo '<code><strong>ID</strong>: ' . $g->id . '<br />' .
			'<strong>Name</strong>: ' . $g->name . '</code>';
		}
		else
		{
			echo '<p><b>Group has already been created</b></p>';
		}	
	
		echo '<hr />';
		echo '<p>Create a couple more groups.</p>';

		echo '<code>
		$g = new Group();<br />
		$g->name = "Moderator";<br />
		$g->save();<br />
		<br />
		$g = new Group();<br />
		$g->name = "Member";<br />
		$g->save();</code>';

		$g = new Group();
		$g->name = 'Moderator';
		$g->save();
		
		$g = new Group();
		$g->name = 'Member';
		$g->save();
		
		echo '<hr />';
		echo '<p>Get all groups and show them.</p>';
		
		echo '<code>// Get all groups<br />
		$g = new Group();<br />
		$g->get();<br />
		<br />
		foreach ($g->all as $group)<br />
		{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;echo "&lt;code&gt;<strong>ID</strong>: " . $group->id . "&lt;br /&gt;" .<br />
		&nbsp;&nbsp;&nbsp;&nbsp;"<strong>Name</strong>: " . $group->name . "&lt;/code&gt;&lt;br /&gt;&lt;br /&gt;";<br />
		}</code>';
		
		echo '<br />';
		echo '<p>This produces:</p>';

		// Get all groups
		$g = new Group();
		$g->get();
			
		foreach ($g->all as $group)
		{
			echo '<code><strong>ID</strong>: ' . $group->id . '<br />' .
				'<strong>Name</strong>: ' . $group->name . '</code><br /><br />';
		}
		
		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Update User
	 */
	function update_user()
	{
		echo '<h1>Update User</h1>';
		
		echo '<p>Here we get an existing User object.</p>';
		echo '<code>
		// Get first User<br />
		$u = new User();<br />
		$u->limit(1)->get();<code>';

		// Get first User
		$u = new User();
		$u->limit(1)->get();

		echo '<br />';
		echo '<p>Current values:</p>';
		echo '<code><strong>ID</strong>: ' . $u->id . '<br />' .
		'<strong>Username</strong>: ' . $u->username . '<br />' .
		'<strong>Email</strong>: ' . $u->email . '<br />' .
		'<strong>Password</strong>: ' . $u->password . '<br />' .
		'<strong>Confirm Password</strong>: ' . $u->confirm_password . '<br />' .
		'<strong>Salt</strong>: ' . $u->salt . '<br />' .
		'<strong>Created</strong>: ' . $u->created . '<br />' .
		'<strong>Updated</strong>: ' . $u->updated . '</code>';
		
		echo '<hr />';		
		echo '<p>Lets reverse the letters of his username and save it.</p>';
		
		echo '<code>
		// Reverse Username<br />
		$u->username = strrev($u->username);<br />
		$u->save();</code>';
		
		$u->username = strrev($u->username);
		$u->save();
		
		echo '<br />';
		echo '<p>Username has been saved as:</p>';
		echo '<code><strong>Username</strong>: ' . $u->username . '</code>';
		
		echo '<br />';
		echo '<p><a href="' . site_url('examples/update_user') . '">Reload the page</a> to see it retrieve the reversed name, then reverse it back.</p>';
		
		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Add Relationships
	 */
	function add_relationships()
	{
		echo '<h1>Add Relationships</h1>';
		
		echo '<p>Here we get an existing User object.</p>';
		echo '<code>
		// Get first User<br />
		$u = new User();<br />
		$u->limit(1)->get();</code>';

		// Get first User
		$u = new User();
		$u->limit(1)->get();
		
		echo '<hr />';		
		echo '<p>Here we get an existing Group object.</p>';
		
		echo '<code>
		// Get first Group<br />
		$g = new Group();<br />
		$g->limit(1)->get();</code>';
		
		// Get first Group
		$g = new Group();
		$g->limit(1)->get();
		
		echo '<hr />';		
		echo '<p>Now we\'ll relate User 1 to Group 1.</p>';
		
		echo '<code>
		// Relate User 1 to Group 1<br />
		$u->save($g);</code>';
		
		// Relation User 1 to Group 1
		$u->save($g);
		
		echo '<hr />';
		
		echo '<p>Get User 2 and 3 and relate them to Group 2.</p>';
		echo '<code>
		// Get Users 2 and 3<br />
		$u = new User();
		$u->limit(2, 1)->get();<br />
		<br />
		// Get Group 2<br />
		$g = new Group();
		$g->where(\'id\', 2)->get();</code>';

		// Get Users 2 and 3
		$u = new User();
		$u->limit(2, 1)->get();
		
		// Get Group 2
		$g = new Group();
		$g->where('id', 2)->get();
		
		echo '<br />';		
		echo '<p>Now we\'ll relate Users 2 and 3 to Group 2.</p>';
		
		echo '<code>
		// Relate Users 2 and 3 to Group 2<br />
		$g->save($u->all);</code>';
		
		// Relate Users 2 and 3 to Group 2<br />
		$g->save($u->all);
		
		echo '<hr />';
		echo '<p>Alright, now lets look at the objects to view the relationships.</p>';
		
		echo '<code>
		// Get first User<br />
		$u = new User();
		$u->limit(1)->get();<br />
		<br />
		// Get related group<br />
		$u->group->get();<br />
		<br />
		echo "&lt;p&gt;" . $u->username . "  belongs to Group " . $u->group->id . ", which is the " . $u->group->name . " group.&lt;/p&gt;";<br />
		<br />
		// Get User 2<br />
		$u = new User();
		$u->where(\'username\', \'Jayne Doe\')->get();<br />
		<br />
		// Get related group<br />
		$u->group->get();<br />
		<br />
		echo "&lt;p&gt;" . $u->username . " belongs to Group " . $u->group->id . ", which is " . $u->group->name . " group.&lt;/p&gt;";<br />
		<br />
		echo "&lt;p&gt;Lets see what other users relate to the " . $u->group->name . " group.&lt;/p&gt;";<br />
		<br />
		foreach ($u->group->user->get()->all as $user)<br />
		{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;if ($user->id != $u->id)<br />
		&nbsp;&nbsp;&nbsp;&nbsp;{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo "&lt;p&gt;" . $user->username . " also belongs to the " . $u->group->name ." group.&lt;/p&gt;";<br />
		&nbsp;&nbsp;&nbsp;&nbsp;}<br />
		}<br />
		</code>';
		
		// Get frst User
		$u = new User();
		$u->limit(1)->get();

		// Get related group
		$u->group->get();

		echo '<p>' . $u->username . '  belongs to Group ' . $u->group->id . ', which is the ' . $u->group->name . ' group.</p>';
		
		// Get User 2
		$u = new User();
		$u->where('username', 'Jayne Doe')->get();
		
		// Get related group
		$u->group->get();

		echo '<p>' . $u->username . ' belongs to Group ' . $u->group->id . ', which is the ' . $u->group->name . ' group.</p>';
		
		echo '<p>Lets see what other users relate to the ' . $u->group->name . ' group.</p>';
		
		foreach ($u->group->user->get()->all as $user)
		{
			if ($user->id != $u->id)
			{
				echo '<p>&nbsp;&nbsp;&nbsp;&nbsp;' . $user->username . ' also belongs to the ' . $u->group->name .' group.</p>';
			}
		}

		echo '<hr />';

		echo '<p>The total counts:</p>';

		echo ucfirst(plural($u)) . ': ' . $u->count() . '<br />';
		echo ucfirst(plural($g)) . ': ' . $g->count() . '<br />';
		echo 'The ' . $g->name . ' ' . $g . ' has ' . $g->user->count() . ' ' . plural($g->user) . '.<br />';

		echo '<hr />';

		
		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Remove Relationships
	 */
	function remove_relationships()
	{
		echo '<h1>Remove Relationship</h1>';
		
		echo '<p>Here we get an existing Group object and view its current relations.</p>';
		echo '<code>
		// Get Group 2<br />
		$g = new Group();<br />
		$g->where(\'id\', 2)->get();</code>';

		// Get Group
		$g = new Group();
		$g->where('id', 2)->get();
		
		echo '<hr />';		
		echo '<p>Now lets loop through and see what users are related.</p>';
		
		echo '<code>
		// Get related users<br />
		$g->user->get();<br />
		<br />
		// Show User relations for Group 2<br />
		foreach ($g->user->all as $u)<br />
		{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;echo "&lt;p&gt;" . $u->username . " is related to " . $g->name . ".&lt;/p&gt;";<br />
		}</code>';

		// Get related users
		$g->user->get();
		
		if (empty($g->user->all))
		{
			echo '<p><b>No Relationships</b></p>';
			die('<p>Run <a href="' . site_url('examples/add_relationships') . '">Add Relationships</a> to add the relations.</p>');
		}
		else
		{
			// Show User relations for Group 2
			foreach ($g->user->all as $u)
			{
				echo '<p>' . $u->username . ' is related to ' . $g->name . '.</p>';
			}
		}
			
		echo '<hr />';		
		echo '<p>Now we\'ll delete the relations.</p>';
		
		echo '<code>
		// Remove relations from Group 2<br />
		$g->delete($g->user->all);</code>';
		
		// Remove relations from Group 2
		$g->delete($g->user->all);
		
		echo '<hr />';
		
		echo '<p>If we check the related users again, it will be empty.</p>';

		echo '<code>
		// Check relations<br />
		if (empty($g->user->all))<br />
		{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;echo "&lt;p&gt;No users are related to the " . $g->name . " group.&lt;/p&gt;";<br />
		}</code>';

		// Check relations<br />
		if (empty($g->user->all))
		{
			echo "<p>&nbsp;&nbsp;&nbsp;&nbsp;No users are related to the " . $g->name . " group.</p>";
		}
		else
		{
			// Show User relations for Group 2
			foreach ($g->user->all as $u)
			{
				echo '<p>' . $u->username . ' is related to ' . $g->name . '.</p>';
			}
		}

		echo '<p>Run <a href="' . site_url('examples/add_relationships') . '">Add Relationships</a> to add the relations back in.</p>';
		
		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Delete User
	 */
	function delete_user()
	{
		echo '<h1>Delete User</h1>';
		
		echo '<p>Here we get an existing User object.</p>';
		echo '<code>
		// Get User<br />
		$u = new User();<br />
		$u->where(\'email\', \'joe@public.com\')->get();</code>';

		// Get User
		$u = new User();
		$u->where('email', 'joe@public.com')->get();

		echo '<br />';
		echo '<p>Current values:</p>';
		echo '<code><strong>ID</strong>: ' . $u->id . '<br />' .
		'<strong>Username</strong>: ' . $u->username . '<br />' .
		'<strong>Email</strong>: ' . $u->email . '<br />' .
		'<strong>Password</strong>: ' . $u->password . '<br />' .
		'<strong>Confirm Password</strong>: ' . $u->confirm_password . '<br />' .
		'<strong>Salt</strong>: ' . $u->salt . '</code>';
		
		echo '<hr />';		
		echo '<p>Let\'s see if it is related to a Group.</p>';
		
		echo '<code>
		// Get related group<br />
		$u->group->get();<br />
		<br />
		// Show related group<br />
		echo "&lt;p&gt;" . $u->username . " is related to " . $u->group->name . ".&lt;/p&gt;";<br />
		</code>';
		
		// Get related group
		$u->group->get();

		if (empty($u->group->name))
		{
			echo '<p><b>No Relationships</b></p>';
			echo '<p>Run <a href="' . site_url('examples/add_relationships') . '">Add Relationships</a> to add the relations.</p>';
		}
		else
		{
			// Show related group
			echo '<p>' . $u->username . ' is related to ' . $u->group->name . '</p>';
		}
	
		echo '<hr />';
		echo '<p>Now we\'ll delete the User. This will remove any existing relationships.</p>';

		echo '<code>
		// Delete User<br />
		$u->delete();<br />
		</code>';
		
		$u->delete();
		
		echo '<br />';
		echo '<p>Current values:</p>';
		echo '<code><strong>ID</strong>: ' . $u->id . '<br />' .
		'<strong>Username</strong>: ' . $u->username . '<br />' .
		'<strong>Email</strong>: ' . $u->email . '<br />' .
		'<strong>Password</strong>: ' . $u->password . '<br />' .
		'<strong>Confirm Password</strong>: ' . $u->confirm_password . '<br />' .
		'<strong>Salt</strong>: ' . $u->salt . '</code>';
		
		echo '<br />';
		echo '<p>Go to the <a href="' . site_url('examples/create_users') . '">Create Users</a> page to add him back in.</p>';
		
		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}

	// --------------------------------------------------------------------

	/**
	 * Self Referencing
	 */
	function self_referencing()
	{
		echo '<h1>Self Referencing Relationships</h1>';
		
		echo '<p>DataMapper allows you to have a model setup with a relationship to its self (hence, self referencing).</p>';

		echo '<p>Take a look at the Employee, Manager, Supervisor, and Underling models included in the DataMapper download.  You\'ll notice they all have references back to the employees table (with their inheritance of the Employee model). The relationships they\'re setup with are:</p>';

		echo '<ul><li>A Manager manages Many Supervisors.</li><li>A Supervisor has One Manager.</li><li>A Supervisor supervises Many Underlings.</li><li>An Underling has One Supervisor.</li></ul>';

		echo '<hr />';

		echo '<p>Here we create a number of different types of employees.</p>';

		echo '<code>
		// Create Manager<br />
		$m = new Manager();<br />
		$m->first_name = \'Jake\';<br />
		$m->last_name = \'Ronalds\';<br />
		$m->position = \'Manager\';<br />
		$m->save();<br />
		<br />
		<br />
		// Create Supervisors<br />
		$s = new Supervisor();<br />
		$s->first_name = \'Bob\';<br />
		$s->last_name = \'Thomas\';<br />
		$s->position = \'Supervisor\';<br />
		$s->save();<br />
		<br />
		$s = new Supervisor();<br />
		$s->first_name = \'Sarah\';<br />
		$s->last_name = \'Parker\';<br />
		$s->position = \'Supervisor\';<br />
		$s->save();<br />
		<br />
		<br />
		// Create Underlings<br />
		$u = new Underling();<br />
		$u->first_name = \'Fred\';<br />
		$u->last_name = \'Smith\';<br />
		$u->position = \'Underling\';<br />
		$u->save();<br />
		<br />
		$u = new Underling();<br />
		$u->first_name = \'Jayne\';<br />
		$u->last_name = \'Doe\';<br />
		$u->position = \'Underling\';<br />
		$u->save();<br />
		<br />
		$u = new Underling();<br />
		$u->first_name = \'Joe\';<br />
		$u->last_name = \'Public\';<br />
		$u->position = \'Underling\';<br />
		$u->save();<br />
		<br />
		$u = new Underling();<br />
		$u->first_name = \'Sam\';<br />
		$u->last_name = \'Rogers\';<br />
		$u->position = \'Underling\';<br />
		$u->save();</code>';



		// Create Manager
		$m = new Manager();
		$m->first_name = 'Jake';
		$m->last_name = 'Ronalds';
		$m->position = 'Manager';
		$m->save();


		// Create Supervisors
		$s = new Supervisor();
		$s->first_name = 'Bob';
		$s->last_name = 'Thomas';
		$s->position = 'Supervisor';
		$s->save();

		$s = new Supervisor();
		$s->first_name = 'Sarah';
		$s->last_name = 'Parker';
		$s->position = 'Supervisor';
		$s->save();


		// Create Underlings
		$u = new Underling();
		$u->first_name = 'Fred';
		$u->last_name = 'Smith';
		$u->position = 'Underling';
		$u->save();

		$u = new Underling();
		$u->first_name = 'Jayne';
		$u->last_name = 'Doe';
		$u->position = 'Underling';
		$u->save();

		$u = new Underling();
		$u->first_name = 'Joe';
		$u->last_name = 'Public';
		$u->position = 'Underling';
		$u->save();

		$u = new Underling();
		$u->first_name = 'Sam';
		$u->last_name = 'Rogers';
		$u->position = 'Underling';
		$u->save();

		echo '<hr />';

		echo '<p>Now we\'ll set up some relationships.</p>';

		echo '<code>// Get the first Supervisor<br />
		$s = new Supervisor();<br />
		$s->get(1);<br />
		<br />
		// Get first 2 Underlings<br />
		$u = new Underling();<br />
		$u->get(2);<br />
		<br />
		// Setup Supervisor to supervise those Underlings<br />
		$s->save($u->all);<br />
		<br />
		<br />
		// Get the second Supervisor<br />
		$s = new Supervisor();<br />
		$s->get(1, 1);<br />
		<br />
		// Get the other 2 Underlings<br />
		$u = new Underling();<br />
		$u->get(2, 2);<br />
		<br />
		// Setup Supervisor to supervise those Underlings<br />
		$s->save($u->all);<br />
		<br />
		<br />
		// Get the Manager<br />
		$m = new Manager();<br />
		$m->get();<br />
		<br />
		// Get the Supervisors<br />
		$s = new Supervisor();<br />
		$s->get();<br />
		<br />
		// Setup Manager to manage those Supervisors<br />
		$m->save($s->all);</code>';

		// Get the first Supervisor
		$s = new Supervisor();
		$s->get(1);

		// Get first 2 Underlings
		$u = new Underling();
		$u->get(2);
		
		// Setup Supervisor to supervise those Underlings
		$s->save($u->all);


		// Get the second Supervisor
		$s = new Supervisor();
		$s->get(1, 1);

		// Get the other 2 Underlings
		$u = new Underling();
		$u->get(2, 2);
		
		// Setup Supervisor to supervise those Underlings
		$s->save($u->all);


		// Get the Manager
		$m = new Manager();
		$m->get();

		// Get the Supervisors
		$s = new Supervisor();
		$s->get();
		
		// Setup Manager to manage those Supervisors
		$m->save($s->all);

		echo '<hr />';

		echo '<p>Now that we\'ve got our relationships setup, let\'s show how they\'re related to each other:</p>';

		echo '<code>$m = new Manager();<br />
		$m->get();<br />
		<br />
		echo $m->full_name() . \' is a \' . $m->position . \' who manages these Supervisors:&lt;br /&gt;\';<br />
		<br />
		$m->supervisor->get();<br />
		<br />
		foreach ($m->supervisor->all as $s)<br />
		{
		&nbsp;&nbsp;&nbsp;&nbsp;echo \'&nbsp;&nbsp;&nbsp;&nbsp;\' . $s->full_name() . \' is a \' . $s->position . \' who supervises these Underlings:&lt;br /&gt;\';<br />
			<br />
		&nbsp;&nbsp;&nbsp;&nbsp;$s->underling->get();<br />
			<br />
		&nbsp;&nbsp;&nbsp;&nbsp;foreach ($s->underling->all as $u)<br />
		&nbsp;&nbsp;&nbsp;&nbsp;{<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo \'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\' . $u->full_name() . \' is an \' . $u->position . \'&lt;br /&gt;\';<br />
		&nbsp;&nbsp;&nbsp;&nbsp;}<br />
		}</code>';

		echo '<hr />';

		echo '<p>This produces:</p>';

		$m = new Manager();
		$m->get();

		echo $m->full_name() . ' is a ' . $m->position . ' who manages these Supervisors:<br />';

		$m->supervisor->get();

		foreach ($m->supervisor->all as $s)
		{
			echo '&nbsp;&nbsp;&nbsp;&nbsp;' . $s->full_name() . ' is a ' . $s->position . ' who supervises these Underlings:<br />';

			$s->underling->get();

			foreach ($s->underling->all as $u)
			{
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $u->full_name() . ' is an ' . $u->position . '<br />';
			}
		}

		echo '<hr />';

		echo '<p>If we wanted to look at Sam Rogers\' supervisor and their supervisors manager, we could do:</p>';

		echo '<code>$u = new Underling();<br />
		$u->where(\'first_name\', \'Sam\')->where(\'last_name\', \'Rogers\')->get();<br />
		<br />
		$u->supervisor->get();<br />
		$u->supervisor->manager->get();<br />
		<br />
		echo $u->full_name . \' is supervised by \' . $u->supervisor->full_name() . \' who is in turn managed by \' . $u->supervisor->manager->full_name() . \'&lt;br /&gt;\';</code>';

		echo '<hr />';

		echo '<p>This produces:</p>';

		$u = new Underling();
		$u->where('first_name', 'Sam')->where('last_name', 'Rogers')->get();

		$u->supervisor->get();
		$u->supervisor->manager->get();

		echo $u->full_name() . ' is supervised by ' . $u->supervisor->full_name() . ' who is in turn managed by ' . $u->supervisor->manager->full_name() . '<br />';

		echo '<hr />';

		echo '<p>Ok, so let\'s do something different and show the total number of Managers:</p>';

		echo '<code>echo $m . \' count: \' . $m->count() . \'&nbsp;br /&nbsp;\';</code>';

		echo '<hr />';

		echo '<p>This produces:</p>';

		echo $m . ' count: ' . $m->count() . '<br />';

		echo '<hr />';

		echo '<p>Now let\'s show how many supervisors are related to ' . $m . ' ' . $m->full_name() . ':</p>';
		
		echo '<code>echo $m . \' \' . $m->full_name() . \' supervises \' .  $m->supervisor->count() . \' \' . plural($m->supervisor) . \'&lt;br /&gt;\' ;</code>';

		echo '<hr />';

		echo '<p>This produces:</p>';

		echo $m . ' ' . $m->full_name() . ' manages ' .  $m->supervisor->count() . ' ' . plural($m->supervisor) . '.<br />';

		echo '<hr />';

		echo '<p>The total counts:</p>';

		echo ucfirst(plural($m)) . ': ' . $m->count() . '<br />';
		echo ucfirst(plural($s)) . ': ' . $s->count() . '<br />';
		echo ucfirst(plural($u)) . ': ' . $u->count() . '<br />';

		echo '<p><a href="' . site_url('examples') . '">Back to Examples</a></p>';
	}
}

/* End of file examples.php */
/* Location: ./application/controllers/examples.php */
