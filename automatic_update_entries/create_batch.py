#!/usr/bin/python
# coding: utf-8

import os
import mysql.connector as sqlconn
import optparse
import sys
from random import randint

def create_batch(hostname, username, password, database):

	NUM_ITEMS_PER_BATCH = 10

	conn = sqlconn.connect(user=username, password=password, host=hostname, database=database)
	cursor = conn.cursor()

	#ADD TRANSCRIPTION GAME BATCHES -> id_task = 1 for TRANSCRIPTION GAME
	query = ("SELECT t.id_image, t.id_transcription FROM transcription t INNER JOIN task_image ti ON t.id_transcription = ti.id_validation AND t.id_image = ti.id_image WHERE t.used_in_batch = 0 AND ti.id_task = 1 AND (ti.state LIKE 1 OR ti.state LIKE 4 OR ti.state LIKE 9 OR ti.state LIKE 10) ORDER BY RAND(%s)")
	data = (randint(1, 196),)

	cursor.execute(query, data)
	rows = cursor.fetchall()
	pos = 0

	ID_IMAGE = 0
	ID_TRANSCRIPTION = 1

	query = ("SELECT t.id_image, t.id_transcription FROM transcription t INNER JOIN task_image ti ON t.id_transcription = ti.id_validation AND t.id_image = ti.id_image WHERE t.used_in_batch = 0 AND ti.id_task = 1 AND (ti.state LIKE 6 OR ti.state LIKE 11) ORDER BY RAND(%s)")
	data = (randint(1, 196),)

	cursor.execute(query, data)
	golden_tasks = cursor.fetchall()
	pos_golden = 0

	for i in range(1,5):
		goldens = 10-(i*2)
		query = ("SELECT id_batch FROM batch WHERE id_task = 1 AND active = 1 AND golden_tasks = %s")
		data = (goldens,)

		cursor.execute(query, data)

		if cursor.fetchone() == None:
			
			query = ("INSERT INTO batch(id_task, n_images, active, golden_tasks) VALUES(%s, %s, %s, %s)")
			data = (1, "10", "1", str(goldens))
			cursor.execute(query, data)

			query = ("SELECT LAST_INSERT_ID();")
			cursor.execute(query)

			(id_batch,) = cursor.fetchone()

			for _ in range(goldens):
				id_image = golden_tasks[pos_golden][ID_IMAGE]
				id_transcription = golden_tasks[pos_golden][ID_TRANSCRIPTION]
				pos_golden += 1
				if pos_golden >= len(golden_tasks):
					pos_golden = 0

				query = ("UPDATE transcription SET used_in_batch = %s WHERE id_transcription = %s")
				data = (1, id_transcription)
				cursor.execute(query, data)
				
				query = ("INSERT INTO batch_image(id_batch, id_image, id_validation) VALUES(%s, %s, %s)")
				data = (id_batch, id_image, id_transcription)
				cursor.execute(query, data)

			for _ in range(10 - goldens):
				id_image = rows[pos][ID_IMAGE]
				id_transcription = rows[pos][ID_TRANSCRIPTION]
				pos += 1
				if pos >= len(rows):
					pos = 0

				query = ("UPDATE transcription SET used_in_batch = %s WHERE id_transcription = %s")
				data = (1, id_transcription)
				cursor.execute(query, data)
				
				query = ("INSERT INTO batch_image(id_batch, id_image, id_validation) VALUES(%s, %s, %s)")
				data = (id_batch, id_image, id_transcription)
				cursor.execute(query, data)

	conn.commit()

	query = ("SELECT COUNT(id_batch) FROM batch WHERE id_task = 1 AND active = 1")

	cursor.execute(query)
	(count,) = cursor.fetchone()
	create_num_batch = 21 - int(count)
	if create_num_batch > 0:

		for num_batch in range(create_num_batch):
			query = ("INSERT INTO batch(id_task, n_images, active) VALUES(%s, %s, %s)")
			data = (1, "10", "1")
			cursor.execute(query, data)

			query = ("SELECT LAST_INSERT_ID();")
			cursor.execute(query)

			(id_batch,) = cursor.fetchone()

			for images in range(NUM_ITEMS_PER_BATCH):
				id_image = rows[pos][ID_IMAGE]
				id_transcription = rows[pos][ID_TRANSCRIPTION]
				pos += 1
				if pos >= len(rows):
					pos = 0
				images += 1

				query = ("UPDATE transcription SET used_in_batch = %s WHERE id_transcription = %s")
				data = (1, id_transcription)
				cursor.execute(query, data)
				
				query = ("INSERT INTO batch_image(id_batch, id_image, id_validation) VALUES(%s, %s, %s)")
				data = (id_batch, id_image, id_transcription)
				cursor.execute(query, data)

	conn.commit()

	#ADD CLUSTER GAME BATCHES -> id_task = 2 for CLUSTER GAME
	query = ("SELECT c.id_cluster FROM cluster c INNER JOIN task_image ti ON c.id_cluster = ti.id_validation WHERE c.used_in_batch = 0 AND ti.id_task = 2 AND (ti.state LIKE 1 OR ti.state LIKE 9) GROUP BY c.id_cluster ORDER BY RAND(%s)")
	data = (randint(1,196),)

	cursor.execute(query, data)
	rows = [i for (i,) in cursor.fetchall()]
	pos = 0

	query = ("SELECT c.id_cluster FROM cluster c INNER JOIN task_image ti ON c.id_cluster = ti.id_validation WHERE c.used_in_batch = 0 AND ti.id_task = 2 AND (ti.state LIKE 7 OR ti.state LIKE 8) GROUP BY c.id_cluster ORDER BY RAND(%s)")
	data = (randint(1,196),)

	cursor.execute(query, data)
	golden_tasks = [i for (i,) in cursor.fetchall()]
	pos_golden = 0

	for i in range(1,5):
		goldens = 10-(i*2)
		query = ("SELECT id_batch FROM batch WHERE id_task = 2 AND active = 1 AND golden_tasks = %s")
		data = (goldens,)

		cursor.execute(query, data)

		if cursor.fetchone() == None:
			
			query = ("INSERT INTO batch(id_task, n_images, active, golden_tasks) VALUES(%s, %s, %s, %s)")
			data = (2, "10", "1", str(goldens))
			cursor.execute(query, data)

			query = ("SELECT LAST_INSERT_ID();")
			cursor.execute(query)

			(id_batch,) = cursor.fetchone()
			total_images = 0

			for _ in range(goldens):
				id_cluster = golden_tasks[pos_golden]
				pos_golden += 1
				if pos_golden >= len(golden_tasks):
					pos_golden = 0

				query = ("UPDATE cluster SET used_in_batch = %s WHERE id_cluster = %s")
				data = (1, id_cluster)
				cursor.execute(query, data)

				query = ("SELECT id_image FROM image_cluster WHERE id_cluster = %s")
				data = (id_cluster,)

				cursor.execute(query, data)
				images = [i for (i,) in cursor.fetchall()]
				total_images += len(images)
				
				for id_image in images:
					query = ("INSERT INTO batch_image(id_batch, id_image, id_validation) VALUES(%s, %s, %s)")
					data = (id_batch, id_image, id_cluster)
					cursor.execute(query, data)

			for _ in range(10 - goldens):
				id_cluster = rows[pos]
				pos += 1
				if pos >= len(rows):
					pos = 0

				query = ("UPDATE cluster SET used_in_batch = %s WHERE id_cluster = %s")
				data = (1, id_cluster)
				cursor.execute(query, data)

				query = ("SELECT id_image FROM image_cluster WHERE id_cluster = %s")
				data = (id_cluster,)

				cursor.execute(query, data)
				images = [i for (i,) in cursor.fetchall()]
				total_images += len(images)
				
				for id_image in images:
					query = ("INSERT INTO batch_image(id_batch, id_image, id_validation) VALUES(%s, %s, %s)")
					data = (id_batch, id_image, id_cluster)
					cursor.execute(query, data)

			query = ("UPDATE batch SET n_images = %s WHERE id_batch = %s")
			data = (total_images, id_batch)
			cursor.execute(query, data)

	query = ("SELECT COUNT(id_batch) FROM batch WHERE id_task = 2 AND active = 1")

	cursor.execute(query)
	(count,) = cursor.fetchone()
	create_num_batch = 20 - int(count)
	if create_num_batch > 0:
		for num_batch in range(create_num_batch):
			total_images = 0

			query = ("INSERT INTO batch(id_task, n_images, active) VALUES(%s, %s, %s)")
			data = (2, "10", "1")
			cursor.execute(query, data)

			query = ("SELECT LAST_INSERT_ID();")
			cursor.execute(query)

			(id_batch,) = cursor.fetchone()

			for clusters in range(NUM_ITEMS_PER_BATCH):
				used_in_batch = 1
				while used_in_batch == 1:
					id_cluster = rows[pos]
					pos += 1
					if pos >= len(rows):
						pos = 0
					query = ("SELECT used_in_batch FROM cluster WHERE id_cluster = %s")
					data = (id_cluster,)
					cursor.execute(query, data)
					(used_in_batch,) = cursor.fetchone()
				
				query = ("UPDATE cluster SET used_in_batch = %s WHERE id_cluster = %s")
				data = (1, id_cluster)
				cursor.execute(query, data)

				query = ("SELECT id_image FROM image_cluster WHERE id_cluster = %s")
				data = (id_cluster,)

				cursor.execute(query, data)
				images = [i for (i,) in cursor.fetchall()]
				total_images += len(images)
				
				for id_image in images:
					query = ("INSERT INTO batch_image(id_batch, id_image, id_validation) VALUES(%s, %s, %s)")
					data = (id_batch, id_image, id_cluster)
					cursor.execute(query, data)

			query = ("UPDATE batch SET n_images = %s WHERE id_batch = %s")
			data = (total_images, id_batch)
			cursor.execute(query, data)

			

	conn.commit()
	cursor.close()
	conn.close()

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

	create_batch(options.hostname,
				options.username,
				options.password,
				options.ddbb)

if __name__ == "__main__":
	main(sys.argv[1:])