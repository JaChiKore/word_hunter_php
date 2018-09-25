#!/usr/bin/python
# coding: utf-8

import os
import mysql.connector as sqlconn
import sys
import optparse

def update_user_feedback(hostname, username, password, database):

	NO_GOOD_ANSWER = "none_of_these"
	DECISION = 0.7

	conn = sqlconn.connect(user=username, password=password, host=hostname, database=database)
	cursor = conn.cursor()

	#UPDATE TRANSCRIPTION GAME BATCHES -> id_task = 1 for TRANSCRIPTION GAME
	query = ("""SELECT au.id_image, COUNT(*)
			FROM answer_user AS au INNER JOIN round AS r INNER JOIN batch AS b INNER JOIN batch_image AS bi 
			ON au.id_round = r.id_round AND au.id_user = r.id_user AND r.id_batch = b.id_batch AND b.id_batch = bi.id_batch 
			WHERE au.processed = 0 AND b.id_task = 1 AND b.active = 1
			GROUP BY au.id_image;""")

	cursor.execute(query)
	result = cursor.fetchall()

	ID_IMAGE = 0
	COUNT = 1

	for res in result:
		if res[COUNT] >= 10:
			id_image = res[ID_IMAGE]

			query = ("SELECT answer, COUNT(*) FROM answer_user WHERE id_image = %s AND processed = 0 GROUP BY answer;")
			data = (id_image)

			cursor.execute(query, data)
			answers = cursor.fetchall()

			ANSWER = 0

			query = ("SELECT id_answer_user FROM answer_user WHERE id_image = %s AND processed = 0;")
			data = (id_image)

			cursor.execute(query, data)
			processeds = [i for (i,) in cursor.fetchall()]

			answer = ""
			rep = -1
			total = 0
			for ans in answers:
				total = total + ans[COUNT]
				if ans[COUNT] > rep:
					rep = ans[COUNT]
					answer = ans[ANSWER]

			res = rep/float(total)

			if answer != NO_GOOD_ANSWER and res >= DECISION:
				for proc in processeds:
					query = ("UPDATE answer_user SET processed = 1 WHERE id_answer_user = %s;")
					data = (proc)

					cursor.execute(query, data)
					conn.commit()

					query = ("SELECT state FRORM task_image WHERE id_image = %s;")
					data = (id_image)

					cursor.execute(query, data)
					(out,) = cursor.fetchone()

					if int(out) == 1 or int(out) == 9:
						next_state = 2
					if int(out) == 4 or int(out) == 10:
						next_state = 5

					query = ("UPDATE task_image SET state = %s WHERE id_image = %s;")
					data = (next_state, id_image)

					cursor.execute(query, data)

					conn.commit()
			else:
				query = ("SELECT state FRORM task_image WHERE id_image = %s;")
				data = (id_image)

				cursor.execute(query, data)
				(out,) = cursor.fetchone()

				if int(out) == 1:
					next_state = 9
				if int(out) == 4:
					next_state = 10

				query = ("UPDATE task_image SET state = %s WHERE id_image = %s;")
				data = (next_state, id_image)

				cursor.execute(query, data)

				conn.commit()

	conn.commit()

	#CHECK TRANSCRIPTION BATCHES
	query = ("SELECT id_batch FROM batch WHERE id_task = 1 AND active = 1;")
	cursor.execute(query)

	batches = [i for (i,) in cursor.fetchall()]

	for id_batch in batches:

		query = ("SELECT id_image FROM batch_image WHERE id_batch = %s;")
		data = (id_batch)

		cursor.execute(query, data)
		images = [i for (i,) in cursor.fetchall()]

		count = 0
		for id_image in images:

			query = ("SELECT state FROM task_image WHERE id_image = %s ANd id_task = 1;")
			data = (id_image)

			cursor.execute(query, data)
			state = int(cursor.fetchone())

			if state == 2 or state == 5:
				count += 1

		if count == len(images):
			query = ("UPDATE batch SET active = 0 WHERE id_batch = %s AND id_task = 1;")
			data = (id_batch)

			cursor.execute(query, data)

	conn.commit()

	#CLUSTER ANSWERS CONSTANTS
	ZERO = 0
	ONE = 1
	DIFF = 2
	EQ = 3

	#UPDATE CLUSTER GAME BATCHES -> id_task = 2 for CLUSTER GAME
	query = ("""SELECT r.id_batch, COUNT(*)
			FROM answer_user AS au INNER JOIN round AS r INNER JOIN batch AS b INNER JOIN batch_image AS bi 
			ON au.id_round = r.id_round AND au.id_user = r.id_user AND r.id_batch = b.id_batch AND b.id_batch = bi.id_batch 
			WHERE au.processed = 0 AND b.id_task = 2 AND b.active = 1
			GROUP BY au.id_batch;""")

	cursor.execute(query)
	result = cursor.fetchall()
	ID_BATCH = 0
	COUNT = 1

	for res in result:
		count = res[COUNT]
		if count >= 10:
			id_batch = res[ID_BATCH]

			query = ("""SELECT b.id_validation
					FROM batch AS b INNER JOIN batch_image AS bi 
					ON b.id_batch = bi.id_batch 
					WHERE b.id_batch = %s 
					GROUP BY b.id_validation;""")
			data = (id_batch)

			cursor.execute(query, data)

			id_clusters = [i for (i,) in cursor.fetchall()]

			for id_cluster in id_clusters:

				query = ("SELECT id_image FROM image_cluster WHERE id_cluster = %s;")
				data = (id_cluster)

				cursor.execute(query, data)
				images = [i for (i,) in cursor.fetchall()]

				all_results = []
				total = 0
				for id_image in images:

					query = ("SELECT answer FROM answer_user WHERE id_image = %s AND processed = 0;")
					data = (id_image)

					cursor.execute(query, data)
					res = [i for (i,) in cursor.fetchall()]

					total = total + len(res)
					dic = [0,0,0,0]
					for out in res:
						if out == "0":
							dic[ZERO] += 1
						elif out == "1":
							dic[ONE] += 1
						elif out == "diff":
							dic[DIFF] += 1
						else:
							dic[EQ] += 1

					all_results.append((id_image, dic))
				compare_num = -1
				for (im, res) in all_results:
					highest = max(res)
					index = res.index(highest)

					if index == ONE:
						if highest > compare_num:
							compare_num = highest
							final_result = im
					elif index == DIFF:
						final_result = "diff"
						break
					elif index == EQ:
						final_result = "eq"
						break

				compare_num = highest
				if compare_num/float(total) >= DECISION:
					query = ("UPDATE cluster SET final_result = %s WHERE id_cluster = %s;")
					data = (final_result, id_cluster)

					cursor.execute(query, data)

					for im in images:
						id_image = im['id_image']

						query = ("SELECT id_answer_user FROM answer_user WHERE id_image = %s AND processed = 0;")
						data = (id_image)

						cursor.execute(query, data)
						processeds = [i for (i,) in cursor.fetchall()]

						for proc in processeds:
							query = ("UPDATE answer_user SET processed = 1 WHERE id_answer_user = %s;")
							data = (proc)

							cursor.execute(query, data)
						conn.commit()

						next_state = 4

						query = ("UPDATE task_image SET state = %s WHERE id_image = %s;")
						data = (next_state, id_image)

						cursor.execute(query, data)

						conn.commit()
				else:
					for im in images:
						id_image = im['id_image']

						next_state = 9

						query = ("UPDATE task_image SET state = %s WHERE id_image = %s;")
						data = (next_state, id_image)

						cursor.execute(query, data)

						conn.commit()


				
	conn.commit()

	#CHECK CLUSTER BATCHES
	query = ("SELECT id_batch FROM batch WHERE id_task = 2 AND active = 1;")
	cursor.execute(query)

	batches = [i for (i,) in cursor.fetchall()]

	for id_batch in batches:

		query = ("SELECT id_image FROM batch_image WHERE id_batch = %s;")
		data = (id_batch)

		cursor.execute(query, data)
		images = [i for (i,) in cursor.fetchall()]

		count = 0
		for id_image in images:

			query = ("SELECT state FROM task_image WHERE id_image = %s ANd id_task = 2;")
			data = (id_image)

			cursor.execute(query, data)
			(state,) = cursor.fetchone()
			state = int(state)

			if state == 3 or state == 5:
				count += 1

		if count == len(images):
			query = ("UPDATE batch SET active = 0 WHERE id_batch = %s AND id_task = 2;")
			data = (id_batch)

			cursor.execute(query, data)

	conn.commit()
	cursor.close()
	conn.close()

def create_cluster_batch():

	conn = sqlconn.connect(user=username, password=password, host=hostname, database=database)
	cursor = conn.cursor()


def main(argv):
	parser = optparse.OptionParser()
	parser.add_option('-H', '--Host',
					  dest="hostname",
					  default="158.109.8.91",
					  type="string",
					  help="Specify mysql hostname to connect")

	parser.add_option('-U', '--User', 
					  dest="username",
					  default="jialuo",
					  type="string",
					  help="Specify username to connect")

	parser.add_option('-P', '--Password', 
					  dest="password",
					  default="pass4dag",
					  type="string",
					  help="Specify password to connect")

	parser.add_option('-D', '--Database', 
					  dest="ddbb",
					  default="word_hunter",
					  type="string",
					  help="Specify which database to connect")

	options, remainder = parser.parse_args()

	update_user_feedback(options.hostname,
				options.username,
				options.password,
				options.ddbb)

if __name__ == "__main__":
	main(sys.argv[1:])