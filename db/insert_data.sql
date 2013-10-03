-- PROJECTS



-- COMMENTS

INSERT INTO `dc_comment` (`comment_id`, `post_id`, `comment_dt`, `comment_tz`, `comment_upddt`, `comment_author`, `comment_email`, `comment_site`, `comment_content`, `comment_words`, `comment_ip`, `comment_status`, `comment_spam_status`, `comment_spam_filter`, `comment_trackback`) VALUES 
(100011, 10001, '2006-06-11 22:55:49', 'UTC', '2006-06-11 22:55:49', 'MarieC', '', 'http://www.mariec.net/', '<p>''ce qui me restait sous la main''... on aura tout entendu ! :-C</p>', '', '82.232.3.35', 1, '0', NULL, 0),
(100012, 10001, '2006-06-12 22:55:49', 'UTC', '2006-06-11 22:55:49', 'MarieC', '', 'http://www.mariec.net/', '<p>''ce qui me restait sous la main''... on aura tout entendu ! :-C</p>', '', '82.232.3.35', 1, '0', NULL, 0),
(100013, 10001, '2006-06-13 22:55:49', 'UTC', '2006-06-11 22:55:49', 'MarieC', '', 'http://www.mariec.net/', '<p>''ce qui me restait sous la main''... on aura tout entendu ! :-C</p>', '', '82.232.3.35', 1, '0', NULL, 0),
(100014, 10001, '2006-06-14 22:55:49', 'UTC', '2006-06-11 22:55:49', 'MarieC', '', 'http://www.mariec.net/', '<p>''ce qui me restait sous la main''... on aura tout entendu ! :-C</p>', '', '82.232.3.35', 1, '0', NULL, 0),
(100015, 10001, '2006-06-15 22:55:49', 'UTC', '2006-06-11 22:55:49', 'MarieC', '', 'http://www.mariec.net/', '<p>''ce qui me restait sous la main''... on aura tout entendu ! :-C</p>', '', '82.232.3.35', 1, '0', NULL, 0);


-- MILESTONES

TRUNCATE TABLE `dc_litraak_milestone`;

INSERT INTO `dc_litraak_milestone` (`milestone_id`, `post_id`, `milestone_name`, `milestone_url`, `milestone_desc`, `milestone_dt`, `milestone_tz`, `milestone_status`) VALUES 
(100011, 10001, 'Milestone 1.0', 'mil-1.0', 'This is milestone 1.0', '2009-05-01 22:19:46', 'UTC', 'created'),
(100012, 10001, 'Milestone 2.1', 'mil-2.1', 'This is milestone 2.1', '2009-05-03 22:19:46', 'UTC', 'created'),
(100013, 10001, 'Milestone 1.5', 'mil-1.5', 'This is milestone 1.5', '2009-05-02 22:19:46', 'UTC', 'created'),
(100024, 10002, 'Milestone 1.1', 'mil-1.1', 'This is milestone 1.1', '2009-05-06 22:19:46', 'UTC', 'created'),
(100025, 10002, 'Milestone 1.0', 'mil-1.0', 'This is milestone 1.0', '2009-05-05 22:19:46', 'UTC', 'created'),
(100026, 10002, 'Milestone 0.3', 'mil-0.3', 'This is milestone 0.3', '2009-05-04 22:19:46', 'UTC', 'created'),
(100037, 10003, 'Milestone 3.0', 'mil-3.0', 'This is milestone 3.0', '2009-05-07 22:19:46', 'UTC', 'created');

-- TICKETS

TRUNCATE TABLE `dc_litraak_ticket`;

INSERT INTO `dc_litraak_ticket` (`ticket_id`, `post_id`, `milestone_id`, `ticket_type`, `ticket_title`, `ticket_desc`, `ticket_email`, `ticket_author`, `ticket_dt`, `ticket_tz`, `ticket_upddt`, `ticket_status`) VALUES 
(1000111, 10001, 100011, 0, 'Ticket #1', 'This is ticket n°1', 'tester1@test.dev', 'Tester1', '2009-05-01 07:56:29', 'UTC', '2009-05-01 07:56:29', 0),
(1000112, 10001, 100011, 0, 'Ticket #2', 'This is ticket n°2', 'tester1@test.dev', 'Tester1', '2009-05-02 07:56:29', 'UTC', '2009-05-02 07:56:29', 0),
(1000123, 10001, 100012, 2, 'Ticket #3', 'This is ticket n°3', 'tester2@test.dev', 'Tester2', '2009-05-03 07:56:29', 'UTC', '2009-05-03 07:56:29', 0),
(1000114, 10001, 100011, 1, 'Ticket #4', 'This is ticket n°4', 'tester3@test.dev', 'Tester3', '2009-05-04 07:56:29', 'UTC', '2009-05-04 07:56:29', 0),
(1000125, 10001, 100012, 0, 'Ticket #5', 'This is ticket n°5', 'tester2@test.dev', 'Tester2', '2009-05-05 07:56:29', 'UTC', '2009-05-05 07:56:29', 0),
(1000126, 10001, 100012, 1, 'Ticket #6', 'This is ticket n°6', 'tester1@test.dev', 'Tester1', '2009-05-06 07:56:29', 'UTC', '2009-05-06 07:56:29', 0),
(1000217, 10002, 100024, 2, 'Ticket #7', 'This is ticket n°7', 'tester3@test.dev', 'Tester3', '2009-05-07 07:56:29', 'UTC', '2009-05-07 07:56:29', 0),
(1000218, 10002, 100024, 1, 'Ticket #8', 'This is ticket n°8', 'tester1@test.dev', 'Tester1', '2009-05-08 07:56:29', 'UTC', '2009-05-08 07:56:29', 0),
(1000219, 10002, 100025, 0, 'Ticket #9', 'This is ticket n°9', 'tester3@test.dev', 'Tester3', '2009-05-09 07:56:29', 'UTC', '2009-05-09 07:56:29', 0);

