
#Build date Tuesday 4th of August 2020 18:34:41 PM
#Build revision 1.1

import os
from numpy import genfromtxt
from sys import argv
import pygal

from pygal.style import Style


root = os.path.dirname(os.path.realpath(argv[0]))

#go to script directory

os.chdir(root)

#get one level up
os.chdir("../")

#for debug
#os.chdir("../")

#get data
#values 
arrLine1 = genfromtxt('data/'+argv[1]+'_val.txt', delimiter=';')
#labels
arrLine2= genfromtxt('data/'+argv[1]+'_label.txt',dtype=None, delimiter=';',encoding='utf-8')

arrLine2= map(str,arrLine2)
 

custom_style = Style(

  value_font_size=48)


last_element = round(arrLine1[-1])


gauge = pygal.SolidGauge(style=custom_style,show_legend=False,inner_radius=0.70)
percent_formatter = lambda x: '{:.10g}%'.format(x)
gauge.value_formatter = percent_formatter

gauge.width = 200
gauge.height = 200
gauge.add('Online', [{'value': last_element, 'max_value': 100, 'color': 'green'}])

gauge.render_to_file('pictures/'+argv[1]+'.svg')


