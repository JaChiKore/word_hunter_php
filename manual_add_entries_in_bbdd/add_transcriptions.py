#!/usr/bin/python
# coding: utf-8

import os
import mysql.connector as sqlconn
import optparse
import sys

def add_transcriptions(hostname, username, password, database, txt):

	reader = open(txt, "r")
	conn = sqlconn.connect(user=username, password=password, host=hostname, database=database)
	cursor = conn.cursor()

	for line in reader:
		imagename, transcription, type_trans, golden_task, correct = line.split(" ")
		correct = correct.rstrip()

		query = ("SELECT id_image FROM image WHERE name_cropped_image = %s")
		data = (imagename,)

		cursor.execute(query, data)
		(id_image,) = cursor.fetchone()

		query = ("INSERT INTO transcription(id_image, transcription, type) VALUES(%s, %s, %s)")
		data = (id_image, transcription, type_trans)

		cursor.execute(query, data)

		query = ("SELECT id_transcription FROM transcription WHERE id_image = %s")
		data = (id_image,)

		cursor.execute(query, data)
		(id_transcription,) = cursor.fetchone()

		query = ("INSERT INTO task_image(id_image, id_validation, state, id_task) VALUES(%s, %s, %s, %s)")
		if golden_task == "1":
			if correct == "1":
				state = 11
			else:
				state = 6
		else:
			state = 1
		data = (id_image, id_transcription, state, "1")

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

	parser.add_option('-f', '--file_clusters_txt', 
					  dest="clusters_txt",
					  type="string",
					  help="Relative/absolute path of the TXT file where stores transcriptions in the form 'imagename:transcription:type'")

	options, remainder = parser.parse_args()

	add_transcriptions(options.hostname,
				options.username,
				options.password,
				options.ddbb,
				options.clusters_txt)

if __name__ == "__main__":
	main(sys.argv[1:])