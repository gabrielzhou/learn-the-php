#define EPSILON 1
#define FLOAT_EQ(x,v) (((v - EPSILON) < x) && (x <( v + EPSILON)))

#include <stdio.h>
#include <math.h>
#include <time.h>
#include <float.h>
#include <iostream>
#include <fstream>
#include <vector>
using namespace std;

struct wire{
	string fromName;
	string toName;
	float fromX;
	float fromY;
	float toX;
	float toY;
	float length;
	float gague;
	string colour;
	string fromType;
	string toType;
	string name;
	bool top;
} nullwire;

struct extra{
	string name;
	string type;
	float x;
	float y;
	bool top;
};

vector<wire> wires;
vector<extra> extras;

void trim(string* str){
	string whitespaces (" \t\f\v\n\r");
	size_t found;

	//Trim end
	found=str->find_last_not_of(whitespaces);
	if (found!=string::npos) str->erase(found+1);
		else str->clear();

	//Trim start
	found=str->find_first_not_of(whitespaces);
	if (found!=string::npos) str->erase(0,found);
}

void trimToSpace(string* str){
	string whitespaces (" \t\f\v\n\r");
	size_t found;

	//Trim start
	found=str->find_first_not_of(whitespaces);
	if (found!=string::npos) str->erase(0,found);
		else str->clear();

	//Trim to first whitespace
	found=str->find_first_of(whitespaces);
	if (found!=string::npos) str->erase(found);
}

//Encode a string in %## form
string percentencode(const string &c){
	char hex[4];
	hex[0]='%';
	string encoded="";
	int len = c.length();
	for(int i=0; i<len; i++){
		if (	(c[i]>='0' && c[i]<='9') ||
			(c[i]>='a' && c[i]<='z') ||
			(c[i]>='A' && c[i]<='Z') ||
			(c[i]=='(') || (c[i]==')') || (c[i]=='[') || 
			(c[i]==']') || (c[i]==' ') || (c[i]=='.') || (c[i]=='-')){
			encoded.append(1,c[i]);
		}else{
			sprintf(hex+1,"%X",c[i]);
			encoded.append(hex);
		}
	}
	return encoded;
}

int main(int argc, char *argv[]){
	clock_t start_t=clock();

	string line;
	ifstream wirefile (argv[1]);
	ifstream insfile (argv[2]);
	ofstream comfile (argv[3]);

	bool automatic=false;
	bool top=false;
	int jobsize=0;
	long wirecount=0;

	wire cwire;
	extra cextra;
	double b;
	double r;
	double c;

	if (wirefile.is_open()){
		while (!wirefile.eof()){
			//Clear the current wire entry
			cwire=nullwire;

			//Read a line
			getline(wirefile,line);

			//Check for header data
			if (line.find("Wiring Method : Automatic")!=string::npos){automatic=true;}
			if (line.find("Fixture Size : Bank 1")!=string::npos){jobsize=1;}
			if (line.find("Fixture Size : Bank 2")!=string::npos){jobsize=2;}
			if (line.find("Fixture Size : Full")!=string::npos){jobsize=3;}
			if (line.find("*+*+* Top *+*+*")!=string::npos){top=true;}

			//Else look for wire data
			if(line.size()<7)continue;

			//See if any of the first 6 chars are non-numeric (and ensure it's not all spaces)
			bool nan=false;	bool nonspace=false; for (unsigned int i=0; i<7; i++){
				if (line[i]==' ')continue; nonspace=true;
				if (((line[i]<'0') || (line[i]>'9')) && !(line[i]=='.')){nan=true;break;}}
			if (nan || !nonspace) continue;
		
			//Wires line - process it
			cwire.top=top;
			cwire.length=atof(line.substr(0,6).c_str());
			cwire.gague=atof(line.substr(7,2).c_str());
			cwire.colour=line.substr(10,6);	trim(&cwire.colour);

			if (automatic){
				cwire.fromName=line.substr(17,15);
				if (cwire.length==0){
					cwire.toName=line.substr(33);
					trimToSpace(&cwire.toName);
				}else{
					cwire.toName=line.substr(33,15);
				}				
			}else{
				cwire.fromName=line.substr(22,15);
				cwire.toName=line.substr(45,15);
			}

			//Calculate mm locations from BRC values
			b=atof(cwire.fromName.substr(1,1).c_str());
			r=atof(cwire.fromName.substr(3,5).c_str());
			c=atof(cwire.fromName.substr(9,5).c_str());
			cwire.fromX=((((c+((b==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
			cwire.fromY=(((r*7000)-88725)*0.002544285714)+241.17975;

			if ((cwire.toName[0]=='(') || (cwire.toName[0]=='[')){
				b=atof(cwire.toName.substr(1,1).c_str());
				r=atof(cwire.toName.substr(3,5).c_str());
				c=atof(cwire.toName.substr(9,5).c_str());
				cwire.toX=((((c+((b==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
				cwire.toY=(((r*7000)-88725)*0.002544285714)+241.17975;
			}

			wires.push_back(cwire);

			//cout << cwire.fromName << " (" << cwire.fromX << "x"<< cwire.fromY << ")" << " -> " << cwire.toName << " (" << cwire.toX << "x"<< cwire.toY << ")" << endl;
		}

		wirefile.close();

		wirecount=wires.size();
	}else{
		cout << "Failed to open 'wires'";
		return 1;
	}

	if (insfile.is_open()){
		top=false;
		bool matched;
		double x,y;
		while (!insfile.eof()){
			matched=false;

			//Read a line
			getline(insfile,line);

			//Check for header data
			if (line.find("*+*+* Top *+*+*")!=string::npos){top=true;}
			
			//Check for data
			if (((line[0]=='(') || (line[0]=='[')) && (line[1]!='b')){
				b=atof(line.substr(1,1).c_str());
				r=atof(line.substr(3,5).c_str());
				c=atof(line.substr(9,5).c_str());

				x=((((c+((b==2)?95:0))*-1500)+279111)*-0.002545333333)+741.6966045;
				y=(((r*7000)-88725)*0.002544285714)+241.17975;

				//Try to match data with wire data
				for (long i=0; i<wirecount; i++){
					if ((FLOAT_EQ(wires[i].fromX,x)) && (FLOAT_EQ(wires[i].fromY,y)) && wires[i].top==top){
						try{wires[i].fromType=line.substr(32,8).c_str(); trim(&wires[i].fromType);}
							catch(exception e){wires[i].fromType="";}
						try{wires[i].name=line.substr(48).c_str(); trim(&wires[i].name);}
							catch(exception e){wires[i].name="";}
						matched=true;
					}

					if ((FLOAT_EQ(wires[i].toX,x)) && (FLOAT_EQ(wires[i].toY,y)) && wires[i].top==top){
						try{wires[i].toType=line.substr(32,8).c_str(); trim(&wires[i].toType);}
							catch(exception e){wires[i].toType="";}
						try{wires[i].name=line.substr(48).c_str(); trim(&wires[i].name);}
							catch(exception e){wires[i].name="";}
						matched=true;
					}
				}

				//If the insert didn't match, add it as an extra
				if (!matched){
					try{cextra.type=line.substr(32,8).c_str(); trim(&cextra.type);}
						catch(exception e){cextra.type="";}
					try{cextra.name=line.substr(48).c_str(); trim(&cextra.name);}
						catch(exception e){cextra.name="";}
					cextra.x=x;
					cextra.y=y;
					cextra.top=top;
					extras.push_back(cextra);
				}
			}
		}

		wirefile.close();
	}else{
		cout << "Failed to open 'inserts'";
		return 2;
	}

	//Get some metrics
	double lowX=DBL_MAX;
	double lowY=DBL_MAX;
	double hiX=-DBL_MAX;
	double hiY=-DBL_MAX;
	for (long i=0; i<wirecount; i++){
		if (wires[i].fromX<lowX){lowX=wires[i].fromX;}
		if (wires[i].fromY<lowY){lowY=wires[i].fromY;}
		if (wires[i].fromX>hiX){hiX=wires[i].fromX;}
		if (wires[i].fromY>hiY){hiY=wires[i].fromY;}
		if (wires[i].toType.size()>0){
			if (wires[i].toX<lowX){lowX=wires[i].toX;}
			if (wires[i].toY<lowY){lowY=wires[i].toY;}
			if (wires[i].toX>hiX){hiX=wires[i].toX;}
			if (wires[i].toY>hiY){hiY=wires[i].toY;}
		}
	}
	double width=hiX-lowX;
	double height=hiY-lowY;
	double aspect=height/width;

	//Output header information
	comfile << "H" << "Size:" << jobsize << endl;
	comfile << "H" << "Top:" << (top?"YES":"NO") << endl;
	comfile << "H" << "Wires:" << wirecount << endl;
	comfile << "H" << "Extras:" << extras.size() << endl;
	comfile << "H" << "LowX:" << lowX << endl;
	comfile << "H" << "LowY:" << lowY << endl;
	comfile << "H" << "HiX:" << hiX << endl;
	comfile << "H" << "HiY:" << hiY << endl;
	comfile << "H" << "Width:" << width << endl;
	comfile << "H" << "Height:" << height << endl;
	comfile << "H" << "Aspect:" << aspect << endl;

	//Output wire information
	for (long i=0; i<wirecount; i++){
		comfile << "W" << 	percentencode(wires[i].fromName) << "," << 
					percentencode(wires[i].toName) << "," << 
					wires[i].fromX << "," << 
					wires[i].fromY << "," <<
					wires[i].toX << "," <<
					wires[i].toY << "," <<
					wires[i].length << "," <<
					wires[i].gague << "," <<
					percentencode(wires[i].colour) << "," <<
					percentencode(wires[i].fromType) << "," <<
					percentencode(wires[i].toType) << "," <<
					percentencode(wires[i].name) << "," <<
					(wires[i].top?1:0) << endl;
	}

	for (long i=0; i<extras.size(); i++){
		comfile << "X" <<	extras[i].x << "," <<
					extras[i].y << "," <<
					percentencode(extras[i].type) << "," <<
					percentencode(extras[i].name) << "," <<
					(extras[i].top?1:0) << endl;
	}

	float runtime=(clock()-start_t)/(float)CLOCKS_PER_SEC;
	cout << (runtime) << endl;

	return 0;

	//printf("Hello, World!\n");
}
