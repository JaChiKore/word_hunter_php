#!/usr/bin/python
# coding: utf-8

import os
import mysql.connector as sqlconn
import optparse
import sys

def add_clusters(hostname, username, password, database, txt):

	dic = {}

	reader = open(txt, "r")

	for line in reader:
		cluster, image, golden_task, different = line.split(" ")
		different = different.rstrip()

		if cluster not in dic:
			dic[cluster] = []

		dic[cluster].append((image, golden_task, different))

	conn = sqlconn.connect(user=username, password=password, host=hostname, database=database)
	cursor = conn.cursor()

	for id_cluster, values in dic.iteritems():
		numIm = len(values)
		#Insert entry in to cluster table
		query = ("INSERT INTO cluster(n_images) VALUES(%s)")
		data = (numIm,)

		cursor.execute(query, data)

		query = ("SELECT MAX(id_cluster) FROM cluster WHERE n_images = %s")
		data = (numIm,)
		cursor.execute(query, data)

		(id_cluster,) = cursor.fetchone()

		for (imname, golden_task, different) in values:
			#get image id in the table image
			query = ("SELECT id_image FROM image WHERE name_cropped_image = %s")
			data = (imname,)

			cursor.execute(query, data)
			(id_image,) = cursor.fetchone()

			#Insert entry in to image_cluster table
			query = ("INSERT INTO image_cluster(id_image, id_cluster) VALUES(%s, %s)")
			data = (id_image, id_cluster)

			cursor.execute(query, data)
			
			query = ("INSERT INTO task_image(id_image, id_validation, state, id_task) VALUES(%s, %s, %s, %s)")
			if golden_task == "1":
				if different == "1":
					state = "7"
				else:
					state = "8"
			else:
				state = "1"
			data = (id_image, id_cluster, state, "2")

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
					  help="Relative/absolute path of the TXT file where stores clusters in the form 'cluster_id,imagename'")

	options, remainder = parser.parse_args()

	add_clusters(options.hostname,
				options.username,
				options.password,
				options.ddbb,
				options.clusters_txt)

if __name__ == "__main__":
	main(sys.argv[1:])