#!/usr/bin/python
# coding: utf-8

import os
import mysql.connector as sqlconn
import optparse
import sys

def add_images(hostname, username, password, database, txt, folder):
	reader = open(txt,"r")

	if folder != "none":
		dic = {}
		for imagename in os.listdir(folder):
			ID = imagename.split(",")[1]
			ID = ID.split(".")[0]
			dic[ID] = imagename

	conn = sqlconn.connect(user=username, password=password, host=hostname, database=database)
	cursor = conn.cursor()

	for line in reader:
		cropped_image_name, x1, y1, x2, y2 = line.split(" ")
		original_image_name = ""
		y2 = y2.rstrip()
		
		if folder != "none":
			ID = cropped_image_name.split("_")[1]

			if ID in dic:
				original_image_name = dic[ID]

		query = ("INSERT INTO image(id_font, name_original_image, name_cropped_image, position_x1, position_y1, position_x2, position_y2) VALUES(%s, %s, %s, %s, %s, %s, %s)")
		data = ('1', original_image_name, cropped_image_name, x1, y1, x2, y2)

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

	parser.add_option('-f', '--file_cropped_images_position', 
					  dest="cropped_txt",
					  type="string",
					  help="Relative/absolute path of the TXT file where stores the positions of the cropped images")
	parser.add_option('-d', '--directory_original_images', 
					  dest="folder_orignal",
					  default="none",
					  type="string",
					  help="Relative/absolute path of the folder where are the original page images")

	options, remainder = parser.parse_args()

	add_images(options.hostname,
				options.username,
				options.password,
				options.ddbb,
				options.cropped_txt,
				options.folder_orignal)

if __name__ == "__main__":
	main(sys.argv[1:])