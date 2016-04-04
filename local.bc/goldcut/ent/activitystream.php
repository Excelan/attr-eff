<?php 
/**
with only three MongoDB nodes, can handle 2-3k mixed transactions per second. At 8 nodes, we can handle 12k-15k mixed transactions per second. Cassandra can scale even higher. 250 reads is (or should be) no problem.

DOC http://www.slideshare.net/neocortica/branchout-presotemplate
http://activitystrea.ms/ Actor, Verb and Object with an optional Target. An actor performs an action of type verb on an object in a target
1 notify my followers(other users) on my actions - new post, new like (for post, photo etc), new follow etc
2 notify me on new content in some lists (such content may not belongs to any user or activity feed/timeline)
MESSAGE QUEUE (TREE.TOPICS) urn-any-subscribers vs mq??
NOTIFICATIONS (PRIORITY 1-3)
SUBSCRIPTIONS (AS MAIL FOLDERS. READ/UNREAD). urn-any-id-subscribers - on self(container), has/use many, lists updates.
	tag, category,  ftopic aquire new post, but not new subscriber - concrete subobject or list of container
FOLLOWINGS (AND SELF) ACTIVITY FEED. xml per user with duplicated
	Activity - WHO user ACTION type ON urn
	user create post, comment post, like post, create forum topic, answer in ftopic, group enter

Subscription
Subscription::fanout() - get subscribers list of host object, fanout urn of aquired object or changes in host object itself 

ActivityStream.
ActivityStream::publishAction(Actor, Verb, Object)
all verb list, register new. all yser roles that can be Actors. all general URNs that can be target Objects
create stream/feed object, get list of subscribers, filter list,push update id to they stream lists, notify each on update (with priority scalation)
!!! ActivityStream::publishAction VS Managed {urn: urn-activitystream, action: publish, verb: ..} 
	класс ActivityStream - helper, он дает Verb ID to string Name. может кешировать списки подписчиков и тп. 
	класс может нести в себе реализацию, чтобы менеджер его только вызывал, но вызов шел только через менеджеа и сообщение
	??? ActivityStream itself vs MQrpc vs Workflow vs DirectCRUD - who invoke ActivityStream? 
	MQ будет иметь приоритет над прямым crud. те некторые WF могут вызывать AS:reg.
	MQ after create - after.fpost.create > ActivityStream::publishAction(actor: urn-user-self, verb: "like", object: urn-post-123)
	MQ after update - упростить системы с черновиками - вывести объект до пуьликации в спец drafts, а в основную таблицу публиковать сразу active
	Workflow - каждый выполненный Contract вызвает Verb вида Task"Name"Done
	? urn-activitystream action publish, actor: urn-user-1, verb: "like", object: urn-post-123
	ВЫВОД - ACTIVITYSTREAM - ПОДЧИНЕННАЯ СУЩНОСТЬ. Ее публикация - результат других действий. Нельзя вызвать Activity Like или newPost и создать данные в результате вызова. Нужно создавать данные, а activity будет следствием
	Идеальный вызов для создания activity stream - в MQ и не через rpc, а отдельным процессом, тк fanout всем подписчикам займет ресурсы и время. Еще лучше - сначала создать activity запись в mqrpc, а отдельным процессом выполнить fanout доставку  

	Notification	
Notification::notify(who, onwhat, priority, data) - route to screen or email

Ориентация на системы управления проектами
XML activitystream с дублирвоанными данными или толькo id
XML/XHTML шаблоны объекта в активности
*/

class ActivityStream 
{
	function fanout()
	{
		/**
		TODO batch listdb commands
		ASYNC IN MQ!?
		*/
		if ($notify)
		{
			println($data->to);
			$ton = new URN((string)$data->to);
			$ton->set_list($notify);
			println($ton,1,TERM_VIOLET);
			$notifywho = $ton->resolve();
			printlnd($notifywho,1,TERM_VIOLET);
			foreach ($notifywho->ids as $who)
			{
				$whourn = URN::build($subjectEntity->name, $who, 'notifications');
				$whourn->set_list($notify);
				println($whourn,2,TERM_VIOLET);
			}
		}
	}	
}
?>