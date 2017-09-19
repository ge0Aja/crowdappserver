import random
import pymysql
import sys
import subprocess
import time
import os
import sys
import logging

def trigger_2(x,y,z,val_th,prt_th):
	logging.basicConfig(filename = "crowdApp.log", level = logging.DEBUG)
	global process_start_time
	global last_rate_timestamp
	global db
	global cursor
	global Unique_ID
	global start_asking_questions
	app =x 
	feature = y
	#~ reported_value = val_th
	#~ reported_prt = prt_th
	last_alarm_timestamp = round(long(z)/1000)
	process_start_time = time.time()
	Unique_ID = str(long(z))+"_"+str(x)+"_"+str(y)
	#with open("/var/www/uploads/log.txt","a") as logfile:
	#	logfile.write(str(Unique_ID)+";process started, time:  "+ str(process_start_time)+"\n")
	#	logfile.write(str(Unique_ID)+";process started and app is "+str(x)+" feature is "+str(y)+" time is "+str(z)+"\n")
	#logfile.close()
	logging.info(str(Unique_ID)+";process started, time:  "+ str(process_start_time))
	logging.info(str(Unique_ID)+";process started and app is "+str(x)+" feature is "+str(y)+" time is "+str(z))	
	db = pymysql.connect(host = '127.0.0.1', port = 3306, db='crowdappdb', user='crowdappuser', passwd='PD1GmA6a@HHunYH3')
	if(db):
		#~ with open("/var/www/uploads/log.txt","a") as logfile:
			#~ logfile.write (str(Unique_ID)+";connection successful to database with crowdappuser \n")
		#~ logfile.close()
		logging.info(str(Unique_ID)+";connection successful to database with crowdappuser")
	cursor = db.cursor()
	cursor.execute("SELECT MAX(timestamp) from app_ratings where app_id = "+str(app))
	last_rate_timestamp = cursor.fetchone()
	cursor.close()
	cursor = db.cursor()
	cursor.execute("select value from intervals where interval_type = (select interval_type from features where ft_id = "+str(feature)+")")
	th_interval = cursor.fetchone()
	cursor.close()
	cursor = db.cursor()
	
	#~ with open("/var/www/uploads/log.txt","a") as logfile: 
		#~ logfile.write("Last rate time is "+str(last_rate_timestamp[0]) + " and the interval value is "+str(th_interval[0])+" \n")
	#~ logfile.close()
	
	logging.info("Last rate time is "+str(last_rate_timestamp[0]) + " and the interval value is "+str(th_interval[0]))
	
	if(last_rate_timestamp[0] is None):
		cursor.execute(" SELECT IFNULL(events / total_events,0.0) FROM((SELECT COUNT(*) as events FROM alarms where app_id = " + str(app)+" and ft_id =" + str(feature)+" ) as q1,(SELECT IFNULL(user_count*(("+str(last_alarm_timestamp)+"-time)/round("+str(th_interval[0])+"/1000)),0) AS total_events from((select  count(distinct u.user_id) as user_count from Users u join user_apps ua on ua.user_id = u.user_id where ua.app_id ="+str(app)+" and u.active =1) as q3,(select round(min(timestamp)/1000) as time from alarms where app_id = "+str(app)+" and ft_id = "+str(feature)+") as q5)) as q2)")
	else:
		#~ print(" SELECT IFNULL(events / total_events,0.0) FROM((SELECT COUNT(*) as events FROM alarms where app_id = " + str(app)+" and ft_id = " + str(feature)+" and "+ str(long(last_alarm_timestamp)/1000)+" > "+str(last_rate_timestamp[0])+") as q1,(SELECT IFNULL(user_count*(("+str(last_alarm_timestamp)+"-time)/("+str(th_interval[0])+"/1000)),0) AS total_events from((select  count(distinct u.user_id) as user_count from alarms join Users u on u.user_id = alarms.user_id where app_id ="+str(app)+" and ft_id ="+str(feature)+" and u.active =1) as q3,(select min(timestamp)/1000 as time from alarms where app_id = "+str(app)+" and ft_id = "+feature+"  and  (timestamp/1000) > " +str(last_rate_timestamp[0])+") as q5)) as q2)")
		cursor.execute(" SELECT IFNULL(events / total_events,0.0) FROM((SELECT COUNT(*) as events FROM alarms where app_id = " + str(app)+" and ft_id = " + str(feature)+" and round(timestamp/1000) > "+str(last_rate_timestamp[0])+") as q1,(SELECT IFNULL(user_count*(("+str(last_alarm_timestamp)+"-time)/round("+str(th_interval[0])+"/1000)),0) AS total_events from((select  count(distinct u.user_id) as user_count from Users u join user_apps ua on ua.user_id = u.user_id where app_id ="+str(app)+" and u.active =1) as q3,(select round(min(timestamp)/1000) as time from alarms where app_id = "+str(app)+" and ft_id = "+feature+"  and  round(timestamp/1000) > " +str(last_rate_timestamp[0])+") as q5)) as q2)")
	event_rate = cursor.fetchone()
	cursor.close()
	coin = random.random()
	#~ with open("/var/www/uploads/log.txt","a") as logfile:
			#~ logfile.write(str(Unique_ID)+";raw event_rate is:" + str(event_rate)+"\n")
	#~ logfile.close()
	
	logging.info(str(Unique_ID)+";raw event_rate is:" + str(event_rate))
	
	#if(event_rate[0] is None):
	#	event_rate[0] = 0

	if(float(coin) > float(event_rate[0])): #
		#~ with open("/var/www/uploads/log.txt","a") as logfile: 
			#~ logfile.write(str(Unique_ID)+";the process will not fire with a chance of "+ str(coin) + " more than "+str(event_rate[0])+"\n")
		#~ logfile.close()
		logging.info(str(Unique_ID)+";the process will not fire with a chance of "+ str(coin) + " more than "+str(event_rate[0]))
		cursor.close()
		
	else:
		#~ with open("/var/www/uploads/log.txt","a") as logfile:
			#~ logfile.write(str(Unique_ID)+";the process will fire with a chance of "+ str(coin) + " less than "+str(event_rate[0])+"\n")
		#~ logfile.close()
		logging.info(str(Unique_ID)+";the process will fire with a chance of "+ str(coin) + " less than "+str(event_rate[0]))
		
		cursor = db.cursor()
		cursor.execute("select app_reputation_normalized from Apps where app_id = "+str(app))
		app_reputation = cursor.fetchone()

		cursor.execute("select value from parameters where id = 5")
		g_n = cursor.fetchone()

		cursor.execute("select value from parameters where id = 7")
		C_D = cursor.fetchone()

		cursor.execute("select value from parameters where id = 4")
		G_NC = cursor.fetchone()

		cursor.execute("select value from parameters where id = 6")
		l_m = cursor.fetchone()

		cursor.execute("select value from parameters where id = 9")
		L_MC = cursor.fetchone()

		cursor.execute("select value from parameters where id = 10")
		L_N = cursor.fetchone()

		cursor.execute("select value from parameters where id = 2")
		G_M = cursor.fetchone()

		cursor.close()

		
		#~ with open("/var/www/uploads/log.txt","a") as logfile:
			#~ logfile.write(str(Unique_ID)+";Game parameters are fetched from DB \n")
		#~ logfile.close()
		
		logging.info(str(Unique_ID)+";Game parameters are fetched from DB")
		
		cursor = db.cursor()
		cursor.execute("select AVG(user_reputation) from Users where Users.user_id in (SELECT Users.user_id from Users join user_apps ua on ua.user_id = Users.user_id where ua.app_id = "+str(app)+")")
		Beta = cursor.fetchone()
		cursor.close()
		
		#~ with open("/var/www/uploads/log.txt","a") as logfile:
			#~ logfile.write(str(Unique_ID)+";average user reputation is fetched from db \n")
		#~ logfile.close()
		
		logging.info(str(Unique_ID)+";average user reputation is fetched from db")
		
		rep_th = 1- float(((float(g_n[0]) + float(L_N[0]) +float(C_D[0])) - float(Beta[0])*(float(G_NC[0]) + float(L_N[0]) ) )/ ((float(g_n[0]) + float(L_N[0])  + float(l_m[0]) - float(L_MC[0])) - float(Beta[0])*(float(G_NC[0]) + float(L_N[0]) - float(G_M[0]) - float(L_MC[0]))))
		question_asked = False
		if(app_reputation[0] is None or app_reputation[0] < rep_th):
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";app reputation is "+str(app_reputation[0])+" null or less than threshold "+str(rep_th)+"\n")
			#~ logfile.close()
			
			logging.info(str(Unique_ID)+";app reputation is "+str(app_reputation[0])+" null or less than threshold "+str(rep_th))
			
			cursor = db.cursor()
			cursor.execute("select qt_id from questions_types where type = (select question_type from features where ft_id = "+str(feature)+")")
			qt = cursor.fetchone()
			cursor.close()
			
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";question type is fetched from db "+str(qt[0])+"\n")
			#~ logfile.close()
			
			logging.info(str(Unique_ID)+";question type is fetched from db "+str(qt[0]))
			
			cursor = db.cursor()
			cursor.execute("select value from control where id = 6")
			start_asking_questions = cursor.fetchone()
			cursor.close()
			
			if(int(start_asking_questions[0]) == 1):
				cursor = db.cursor()
				cursor.execute("insert into questions (qt_id,app_id,feature_id,timestamp) values("+str(qt[0])+","+str(app)+","+str(feature)+",UNIX_TIMESTAMP())")
				db.commit()
				question = cursor.lastrowid
				cursor.close()
				question_asked = True
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";question inserted to be used by the users, question # is " +str(question)+"\n")
				#~ logfile.close()		
				
				logging.info(str(Unique_ID)+";question inserted to be used by the users, question # is " +str(question))
					
				#~ with open("/var/www/uploads/log.txt","a") as logfile:	
					#~ logfile.write(str(Unique_ID)+";start asking users \n")
				#~ logfile.close() 
				
				logging.info(str(Unique_ID)+";start asking users")
				
				user_asked = ask_users(app,feature,question)
		else:
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";app reputation was high and will increase the counter for it \n")
			#~ logfile.close()
		 
			logging.info(str(Unique_ID)+";app reputation was high and will increase the counter for it")
			
			cursor = db.cursor()
			cursor.execute("update Apps set rep_counter = rep_counter +1 where app_id = "+str(app))
			db.commit()
			cursor.close()
			cursor = db.cursor()
			cursor.execute("select rep_counter from Apps where app_id ="+str(app))
			rep_counter = cursor.fetchone()
			cursor.close()
			cursor = db.cursor()
			cursor.execute("select value from control where id = 5")
			allowed_evade_times = cursor.fetchone()
			cursor.close()
			renter_fraction = int(rep_counter[0]) / int(allowed_evade_times[0])
			if(renter_fraction > 1):
				renter_fraction = 1
			
			coin = random.random()
			
			if(coin < renter_fraction):
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";the process rentered with a chance of"+str(coin)+" less than "+str(renter_fraction)+"\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";the process rentered with a chance of"+str(coin)+" less than "+str(renter_fraction))

				cursor = db.cursor()
				cursor.execute("update Apps set rep_counter = 0 where app_id = "+str(app))
				db.commit()


				cursor = db.cursor()
				cursor.execute("select qt_id from questions_types where type = (select question_type from features where ft_id = "+str(feature)+")")
				qt = cursor.fetchone()
				
				cursor.execute("select value from control where id = 6")
				start_asking_questions = cursor.fetchone()
				cursor.close()
			
				if(int(start_asking_questions[0]) == 1):
					cursor = db.cursor()
					cursor.execute("insert into questions (qt_id,app_id,feature_id) values("+str(qt[0])+","+str(app)+","+str(feature)+")")
					db.commit()
					question = cursor.lastrowid
					cursor.close()				
					#~ with open("/var/www/uploads/log.txt","a") as logfile:
						#~ logfile.write(str(Unique_ID)+";question inserted to be used by the users, question # is " +str(question)+"\n")
						#~ logfile.write(str(Unique_ID)+";start asking users \n")
					#~ logfile.close()
					
					logging.info(str(Unique_ID)+";question inserted to be used by the users, question # is " +str(question))
					logging.info(str(Unique_ID)+";start asking users")
					
					question_asked = True
					user_asked = ask_users(app,feature,question)
				
		if(question_asked and user_asked):
			cursor  = db.cursor()
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";a question was asked and users were asked the reputation will get updated after waking up...\n")
			#~ logfile.close()
			
			logging.info(str(Unique_ID)+";a question was asked and users were asked the reputation will get updated after waking up...")
			
			cursor.execute("select value from control where id = 4")
			sleep_period = cursor.fetchone()	
			cursor.close()
			#~ db.close()
			
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";will go to sleep for "+str(sleep_period[0])+" minutes \n")
			#~ logfile.close()
			
			logging.info(str(Unique_ID)+";will go to sleep for "+str(sleep_period[0])+" minutes")
			
			db.close()
			time.sleep(sleep_period[0]*60) # sleep_period[0]*60 commented
			
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";db reopen and calculate the reputation \n")
			#~ logfile.close()
			
			logging.info(str(Unique_ID)+";db reopen and calculate the reputation")
			
			db = pymysql.connect(host = '127.0.0.1', port = 3306, db='crowdappdb', user='crowdappuser', passwd='PD1GmA6a@HHunYH3')
			cursor = db.cursor()
			cursor.execute("select count(*) from user_replies where user_replies.q_id in (select uq_id from user_questions where q_id = "+str(question)+")\n")
			replies_count = cursor.fetchone()
			cursor.close()
			
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write("the number of replies for question # "+str(question)+" is "+str(int(replies_count[0]))+"at time" +str(time.time()))
			#~ logfile.close()
			
			logging.info("the number of replies for question # "+str(question)+" is "+str(int(replies_count[0]))+"at time" +str(time.time()))
			
			if(int(replies_count[0]) > 0):
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";"+str(replies_count[0])+" replies were submitted for question #"+str(question)+". The reputation will get updated \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";"+str(replies_count[0])+" replies were submitted for question #"+str(question)+". The reputation will get updated")
				
				cursor = db.cursor()
				cursor.execute("select IFNULL(count(*),0) from user_replies where user_replies.q_id in (select uq_id from user_questions where q_id = "+str(question)+") and r_id =1")
				yes_count = cursor.fetchone()
				cursor.execute("select IFNULL(count(*),0) from user_replies where user_replies.q_id in (select uq_id from user_questions where q_id = "+str(question)+") and r_id =2")
				no_count = cursor.fetchone()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";total number of answers (yes and no) is "+str(int(yes_count[0])+int(no_count[0]))+"\n")
					#~ # logfile.write(str(Unique_ID)+";yes_count is "+str(yes_count[0])+" and no count is "+str(no_count[0])+" the majority is "+str(majority)+" majority count is "+str(majority_count[0])+"\n")
					#~ logfile.write(str(Unique_ID)+";yes_count is "+str(yes_count[0])+" and no count is "+str(no_count[0])+"\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";total number of answers (yes and no) is "+str(int(yes_count[0])+int(no_count[0])))
				logging.info(str(Unique_ID)+";yes_count is "+str(yes_count[0])+" and no count is "+str(no_count[0]))
				
				## added this for the new reputation (based on confidence of the answer of  each user) // Sep 19 2017
				cursor = db.cursor()
			 	cursor.execute("select IFNULL(AVG(answer_rate)/5,0) from user_replies ur join user_questions uq on ur.q_id = uq.uq_id  where uq.q_id in (select uq_id from user_questions where q_id = "+str(question)+") and ur.r_id = 1")
			 	average_crowd_experties_voted_yes = cursor.fetchone() # is the average (yes) answer rating now 
			 	cursor.execute("select IFNULL(AVG(answer_rate)/5,0) from user_replies ur join user_questions uq on ur.q_id = uq.uq_id  where uq.q_id in (select uq_id from user_questions where q_id = "+str(question)+") and ur.r_id = 2")
			 	average_crowd_experties_voted_no = cursor.fetchone() # is the average (no) answer rating now
			 	cursor.close()

				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";Average Crowd Experties : Yes "+ str(average_crowd_experties_voted_yes)+" No "+ str(average_crowd_experties_voted_no)+"\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";Average Answer : Yes "+ str(average_crowd_experties_voted_yes[0])+" No "+ str(average_crowd_experties_voted_no[0]))

				## we should replace beta_prime with beta_yes and beta_no and rep_change will be the new reputaiton for the app and we won't need majority count
				cursor = db.cursor()
				#cursor.execute("insert into app_ratings (question_id,app_id,change_,beta_prime,users_count,timestamp) values ("+str(question)+","+str(app)+","+str(rep_change)+","+str(beta_prime[0])+","+str(majority_count[0])+",UNIX_TIMESTAMP())")
				cursor.execute("insert into app_ratings (question_id,app_id,yes_count,no_count,beta_yes,beta_no,timestamp) values ("+str(question)+","+str(app)+","+str(yes_count[0])+","+str(no_count[0])+","+str(average_crowd_experties_voted_yes[0])+","+str(average_crowd_experties_voted_no[0])+",UNIX_TIMESTAMP())")
				db.commit()
				cursor.close()
				##
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";record inserted to app ratings \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";record inserted to app ratings")

				## this section is added to calculate the new reputation
				cursor = db.cursor()
				cursor.execute("SELECT IFNULL(AVG(yes_count * beta_yes),0) from app_ratings where yes_count <> 0  and question_id in (select q_id from questions where app_id = "+str(app)+" and feature_id = "+str(feature)+")")
				average_yes_count_beta = cursor.fetchone()
				cursor.execute("SELECT IFNULL(AVG(no_count * beta_no),0) from app_ratings where  no_count <> 0 and  question_id in (select q_id from questions where app_id = "+str(app)+" and feature_id = "+str(feature)+")")
				average_no_count_beta = cursor.fetchone()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";the average_yes_count_beta is "+str(average_yes_count_beta[0])+";the average_no_count_beta is "+str(average_no_count_beta[0]) +"\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";the average_yes_count_beta is "+str(average_yes_count_beta[0])+";the average_no_count_beta is "+str(average_no_count_beta[0]))
				
				##
				## added this section to update the row where the new reputation will be stored
				new_reputation = (float(average_yes_count_beta[0]) - float(average_no_count_beta[0])) / (float(average_yes_count_beta[0]) + float(average_no_count_beta[0]))
				##new_reputation_normalized = (new_reputation - (-1)) / (2)
				cursor = db.cursor()
				cursor.execute("update app_ratings set reputation = "+str(new_reputation)+" where question_id = "+str(question))
				db.commit()

				cursor.execute("update Apps set app_reputation =" +str(new_reputation)+" where app_id ="+str(app))
				cursor.commit()

				##, reputation_normalized = "+str(new_reputation_normalized)+"
				cursor.execute ("SELECT MIN(app_reputation) FROM Apps")
				min_reputation_new = cursor.fetchone()

				cursor.execute ("SELECT MAX(app_reputation) FROM Apps")
				max_reputation_new = cursor.fetchone()

				new_reputation_normalized = (new_reputation - float(min_reputation_new[0])) / (float(max_reputation_new[0]) - float(min_reputation_new[0]))
				
				cursor.execute("update app_ratings set reputation_normalized = "+str(new_reputation_normalized)+" where question_id = "+str(question))
				cursor.commit()
				cursor.close()

				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";app reputation "+ str(new_reputation)+" and app reputation normalized "+str(new_reputation_normalized)+" are updated in the app_ratings table\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";app reputation "+ str(new_reputation)+" and app reputation normalized "+str(new_reputation_normalized)+" are updated in the app_ratings table")

				cursor = db.cursor()
				cursor.execute("update Apps set app_reputation_normalized = (app_reputation - min(app_reputation)) / (max(app_reputation) - min(app_reputation))")
				cursor.commit()
				cursor.close()

				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+"; Apps reputations are normalized and stored \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+"; Apps reputations are normalized and stored")

				## New Majority
				cursor = db.cursor()
				cursor.execute("select sum(yes_count) from from app_ratings where question_id in (select q_id from questions where app_id = "+str(app)+" and feature_id = "+str(feature)+")")
				yes_count_total = cursor.fetchone()

				cursor.execute("select sum(no_count) from from app_ratings where question_id in (select q_id from questions where app_id = "+str(app)+" and feature_id = "+str(feature)+")")
				no_count_total = cursor.fetchone()	
				cursor.close()


				if(int(yes_count_total[0]) > int(no_count_total[0])):
					majority = 1
				elif(int(yes_count_total[0]) < int(no_count_total[0])):
					majority = 2
				else:
					majority = 3

				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";Yes total count "+ str(yes_count_total)+" and No total count "+str(no_count_total)+" The majority is "+str(majority)+"\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";Yes total count "+ str(yes_count_total)+" and No total count "+str(no_count_total)+" The majority is "+str(majority))

				##
				cursor = db.cursor()
				cursor.execute("update Users set correct_detection_counter = correct_detection_counter+1 where user_id in (select ur.user_id from user_replies ur join user_questions uq on ur.user_id = uq.user_id where uq.q_id = "+str(question)+" and ur.r_id = "+str(majority)+")")
				db.commit()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";correct detection count updated for users \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";correct detection count updated for users")
				
				cursor = db.cursor()
				cursor.execute("update Users set incorrect_detection_counter = incorrect_detection_counter+1 where user_id in (select ur.user_id from user_replies ur join user_questions uq on ur.user_id = uq.user_id where uq.q_id = "+str(question)+" and ur.r_id <> "+str(majority)+")")
				db.commit()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";incorrect detection count updated for users \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";incorrect detection count updated for users")
				
				#cursor = db.cursor()
				## this needs to be replaced (basically it is just the final reputation)
				#cursor.execute("select IFNULL(sum(beta_prime * change_ * users_count)/sum(beta_prime * users_count),0.0) from app_ratings where app_id = "+str(app))
				new_rep = new_reputation_normalized #cursor.fetchone()
				#cursor.close()
				##
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";the new nomalized reputation is calculated with a value of"+str(new_rep)+"\n") #new_rep[0]
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";the new nomalized reputation is calculated with a value of"+str(new_rep))
				
				cursor = db.cursor()
				# Change the way we update Apps reputation (basically query the last computed reputation)
				#cursor.execute("update Apps set app_reputation = case when app_reputation is null then case when (0.5 + "+str(new_rep[0])+") < 0.0 then 0.0 when (0.5 + "+str(new_rep[0])+") > 1.0 then 1.0 else (0.5+"+str(new_rep[0])+") end else case when (app_reputation+"+str(new_rep[0])+") < 0.0 then 0.0 when (app_reputation+"+str(new_rep[0])+") > 1.0 then 1.0 else (app_reputation+"+str(new_rep[0])+") end end where app_id = "+str(app))
				cursor.execute("update Apps set app_reputation = "+str(new_rep)+ "where app_id = "+str(app))
				db.commit()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";app reputaion is updated \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";app reputaion is updated")
				
				cursor = db.cursor()
				#cursor.execute("update Users set diff = case when ((correct_detection_counter - incorrect_detection_counter) < 0) then 0 when ((correct_detection_counter - incorrect_detection_counter) > 0) then (correct_detection_counter - incorrect_detection_counter) end")
				cursor.execute("update Users set diff = (correct_detection_counter - incorrect_detection_counter)")
				db.commit()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";Users diff is updated \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";Users diff is updated")
				
				cursor = db.cursor()
				cursor.execute("select min(diff) from Users where active = 1")
				min_diff = cursor.fetchone()
				cursor.close()
				cursor = db.cursor()
				cursor.execute("select max(diff) from Users where active = 1")
				max_diff = cursor.fetchone()
				cursor.close()
				
				cursor = db.cursor()
				cursor.execute("update Users set user_reputation = ((diff - "+str(min_diff[0])+") / ("+str(max_diff[0])+" - "+str(min_diff[0])+"))")
				db.commit()
				cursor.close()
				
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";users reputation is updated \n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";users reputation is updated")
				
			else:
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";no replies were submitted for question #"+str(question)+". The reputation will not get updated \n")
				#~ logfile.close()
				logging.info(str(Unique_ID)+";no replies were submitted for question #"+str(question)+". The reputation will not get updated")
		else:
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";no questions were asked \n")
			#~ logfile.close()
			logging.info(str(Unique_ID)+";no questions were asked")
		
		#~ with open("/var/www/uploads/log.txt","a") as logfile:
			#~ time_elapsed = time.time() - process_start_time
			#~ logfile.write(str(Unique_ID)+";The process has finished, elapsed time is "+str(time_elapsed)+"\n\n")
		#~ logfile.close()
		time_elapsed = time.time() - process_start_time
		logging.info(str(Unique_ID)+";The process has finished, elapsed time is "+str(time_elapsed))
		logging.info("============================================================================================================")
		
		db.close()
		sys.exit()
	return None
	
def ask_users(app,feature,question):
	#~ with open("/var/www/uploads/log.txt","a") as logfile:
		#~ logfile.write(str(Unique_ID)+";start asking users function \n")
	#~ logfile.close()
	
	logging.info(str(Unique_ID)+";start asking users function")
	
	cursor = db.cursor()
	if(last_rate_timestamp[0] is None):
		cursor.execute("select u.user_id,avg(al.val) as average_val, avg(al.prt) as average_prt from Users u  join alarms al on u.user_id = al.user_id where al.app_id = "+str(app)+" and al.ft_id = "+str(feature)+" and u.active = 1 and u.user_id not in (select user_id from user_questions join questions q on user_questions.q_id = q.q_id where (UNIX_TIMESTAMP() - user_questions.timestamp < 86400) and q.app_id = "+str(app)+" and q.feature_id = "+str(feature)+" group by user_id) and u.user_id not in (select user_id from user_questions join questions q on user_questions.q_id = q.q_id where (UNIX_TIMESTAMP() - user_questions.timestamp < 3600) group by user_id) and (CURTIME() > CAST('10:00:00' AS TIME) AND CURTIME() < CAST('20:00:00' AS TIME) ) group by u.user_id")
	else:
		cursor.execute("select u.user_id,avg(al.val) as average_val, avg(al.prt) as average_prt from Users u  join alarms al on u.user_id = al.user_id where al.app_id = "+str(app)+" and al.ft_id = "+str(feature)+" and u.active = 1 and al.timestamp > "+str(last_rate_timestamp[0])+" and u.user_id not in (select user_id from user_questions join questions q on user_questions.q_id = q.q_id where (UNIX_TIMESTAMP() - user_questions.timestamp < 86400) and q.app_id = "+str(app)+" and q.feature_id = "+str(feature)+" group by user_id) and u.user_id not in (select user_id from user_questions join questions q on user_questions.q_id = q.q_id where (UNIX_TIMESTAMP() - user_questions.timestamp < 3600) group by user_id) and (CURTIME() > CAST('10:00:00' AS TIME) AND CURTIME() < CAST('20:00:00' AS TIME) )  group by u.user_id")
	
	users = cursor.fetchall()
	
	#~ with open("/var/www/uploads/log.txt","a") as logfile:
		#~ logfile.write(str(Unique_ID)+";users are fetched to be asked \n")
	#~ logfile.close()
	
	logging.info(str(Unique_ID)+";users are fetched to be asked")
	
	user_asked = False
	cursor.execute("select value from control where id = 3")
	allowed_times = cursor.fetchone()
	for user in users:
		cursor.execute("select IFNULL(count(*),0) from user_questions where user_id = "+str(user[0])+" and timestamp > (UNIX_TIMESTAMP() - 86400)")
		times_asked = cursor.fetchone()
		ask_fraction = float(times_asked[0]) / float(allowed_times[0])
		if(ask_fraction > 1.0):
			ask_fraction = 1.0
		
		coin = random.random()
		
		if(coin > 1-ask_fraction):
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";don't ask user with a chance of " + str(coin) + "bigger than"+ str(1-ask_fraction)+" and ask fraction is "+str(ask_fraction) +"\n")
			#~ logfile.close()
			logging.info(str(Unique_ID)+";don't ask user with a chance of " + str(coin) + "bigger than"+ str(1-ask_fraction)+" and ask fraction is "+str(ask_fraction))
		else:
			#~ with open("/var/www/uploads/log.txt","a") as logfile:
				#~ logfile.write(str(Unique_ID)+";ask user "+str(user[0])+" with a chance of " + str(coin)+"\n")
			#~ logfile.close()
			
			logging.info(str(Unique_ID)+";ask user "+str(user[0])+" with a chance of " + str(coin))
			
			cursor.execute("insert into user_questions(q_id,user_id,timestamp) values ("+str(question)+","+str(user[0])+",UNIX_TIMESTAMP())")
			db.commit()
			user_question = cursor.lastrowid
			if(int(user_question) > 0):
				#~ with open("/var/www/uploads/log.txt","a") as logfile:
					#~ logfile.write(str(Unique_ID)+";the inserted user question id for the user "+str(user[0])+" is "+str(user_question)+" with a value of "+str(user[1])+" and a prt of "+str(user[2])+"\n")
					#~  #~ logfile.write(str(Unique_ID)+";test from GEORGY "+"row:"+str(user_question)+" th_val:"+str(reported_value)+" th_prt:"+str(reported_prt)+"\n")
				#~ logfile.close()
				
				logging.info(str(Unique_ID)+";the inserted user question id for the user "+str(user[0])+" is "+str(user_question)+" with a value of "+str(user[1])+" and a prt of "+str(user[2]))
				
				subprocess.call(["php","-f","/var/www/html/CrowdApp/send_not.php","row:"+str(user_question),"th_val:"+str(user[1]),"th_prt:"+str(user[2])])
				user_asked = True
	return user_asked

if __name__ == '__main__':
	trigger_2(*sys.argv[1:])

