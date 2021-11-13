#!/usr/bin/env python3

import os
import re
import requests

def jobFileParse(path):

    version = ''
    version_re = 'protected \$version ='
    method = ''
    method_re = 'protected \$method ='
    route = ''
    route_re = 'protected \$endpoint ='

    for line in open(path, mode='r'):
    
        # Need to get the method, version, and route
        for match in re.finditer(method_re, line):
            m = re.search(r"get|post", line)
            if m is not None:
                method = m.group()

        for match in re.finditer(version_re, line):
            v = re.search(r"\d+", line)
            if v is not None:
                vs = v.group()
                try:
                    version = int(vs)
                except:
                    continue

        for match in re.finditer(route_re, line):
            r = re.search(r"'.+'", line)
            if r is not None:
                route = r.group().replace('\'', '')
    # print(route, version, method)
    return route, version, method
    
    
            

def versionCheck(swagger, path, version, method):
    latestVersion = 0
    if path in swagger["paths"]:
        p = swagger["paths"][path]
        if method in p:
            r = p[method]
            for tag in r["x-alternate-versions"]:
                if tag[0] == "v":
                    try:
                        v = int(tag[1:])
                        if v > latestVersion:
                            latestVersion = v
                    except:
                        continue
            if latestVersion > version:
                return True, latestVersion
            else:
                return False, latestVersion
        else:
            print("unknown method for route - {}".format(path))
            return False, 0
        return False, 0
    else:
        print("unknown path - {}".format(path))
        return False, 0


if __name__ == "__main__":
    
    # First grab the swagger.json to compare against

    swagger = requests.get(url="https://esi.evetech.net/latest/swagger.json").json()

    # Now we need to open each Job file and look for job paths and version numbers
    dir = os.path.join( 'src', 'Jobs')

    for root, d_names, f_names in os.walk(dir):
        if f_names is not None:
            for file in f_names:
                filepath = os.path.join(root, file)
                path, vers, method = jobFileParse(filepath)

                if any([path == '', vers == '', method == '']):
                    continue

                mismatch, inuse = versionCheck(swagger, path, vers, method)
                if mismatch:
                    print("Found non latest route in use")
                    print("\tFile: {}".format(filepath))
                    print("\t\tRoute: {}".format(path))
                    print("\t\tUsed Version: {}".format(vers))
                    print("\t\tSwag Version: {}".format(inuse))