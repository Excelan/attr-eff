#!/usr/bin/env python
import pika
import subprocess
import os
import sys

os.chdir("../workdir")

connection = pika.BlockingConnection(pika.ConnectionParameters(
        host='localhost'))
channel = connection.channel()

channel.queue_declare(queue='hello')

def callback(ch, method, properties, body):
    print(" [x] Received %r" % body)
    canonicalname = body[:-4]
    if sys.version_info > (3, 0):
        canonicalname = canonicalname.decode("utf-8", "strict")
    print(canonicalname)
    try:
        os.unlink('../../tmp/'+canonicalname+'.pdf')
    except:
        pass
    try:
        subprocess.call(["pdflatex", body, "-halt-on-error", "-interaction=nonstopmode"])
    except Exception as e:
        print("Unexpected error in pdflatex subprocess:", e, sys.exc_info()[0])
    try:
        print(canonicalname+'.pdf')
        print('../../tmp/'+canonicalname+'.pdf')
        os.rename(canonicalname+'.pdf', '../../tmp/'+canonicalname+'.pdf')
        print(" [x] DONE %r" % body)
        print(canonicalname)
    except Exception as e:
        print("Unexpected error in move to tmp/*.pdf:", e, sys.exc_info()[0])


channel.basic_consume(callback,
                      queue='hello',
                      no_ack=True)

print(' [*] Waiting for messages. To exit press CTRL+C')
channel.start_consuming()
