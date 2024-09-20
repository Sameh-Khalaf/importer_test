#!/usr/bin/python
import os
import subprocess
from pathlib import Path
from subprocess import check_output, CalledProcessError
import psutil


def getProcessIdByName(processName):
    listOfProcessObjects = []
    for proc in psutil.process_iter():
        try:
            pinfo = proc.as_dict(attrs=['pid', 'name', 'cmdline'])
            for cmd in pinfo['cmdline']:
                split = processName.split(" ")
                if processName in cmd:
                    listOfProcessObjects.append(pinfo)
        except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
            pass
    return listOfProcessObjects;



if __name__ == '__main__':

    myfile = Path("/tmp/terminate_process")
    if myfile.is_file():

        list = getProcessIdByName("autoimport")

        for p in list:
            pid = int(p["pid"])
            print(pid,p['cmdline'])
            subprocess.call("kill -9 %d" % pid, shell=True)

        os.unlink("/tmp/terminate_process")
