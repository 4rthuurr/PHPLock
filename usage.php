<?php
/*
~> example code where we need to iterate trough all clients on our example bank
~> and update their balance, this works in a multi-threaded context where the client may be doing two transactions at the exact same time.
*/

include ('php_lock.php');

function update_balance($client, $amount) {
	$status = false;
	
	$file = $client;
	$client = json_decode(file_get_contents($file)); // get client's json object
	
	$client->balance += $amount; // updates the client's balance on our local json buffer
	
	$lock = new Lock($client->id); // initializes a lock with the client's identifiable ID, waits if a lock already exists.	
	
	if(!$lock->Lock()) // tries to obtain a lock before we do any write operations on the client's real data, preventing race conditions.
		return update_balance($client, $amount); // returns itself because a transaction is probably already ongoing.
		
	if(file_put_contents($file, json_encode($client), LOCK_EX)) // we have full control over the client's data until the lock timeout is reached, so we can do unlimited write operations without fearing problems.
		$status = true;
		
	echo $client->name . "'s current balance: " . number_format($client->balance, 2) . "<br>";
		
	$lock->Unlock(); // Unlocks the lock associated with the client's ID so other threads can continue its job on the specific client.
	
	return $status;
}

$clients = array();

foreach(glob("db/*") as $f) {
    if(!is_dir($f))
		array_push($clients, "db/" . basename($f));
}

foreach($clients as $client)
	update_balance($client, 10);
	
?>