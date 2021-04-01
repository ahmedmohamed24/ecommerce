# currentState  nextState[input0] nextState[input1] firstOutput secondOutput
lookupTable=[
    00 00 01 00 11
    01 10 11 11 00
    10 00 01 10 01
    11 10 11 01 10
]
path1[9]=zeroes();
path2[9]=zeroes();
path3[9]=zeroes();
path4[9]=zeroes();
t=0;
temp=0;
currentState=00;
path1[0]= path2[0]=hammingDistance(00, input);
path3[0]= path4[0] =hammingDistance(01, input);
path1[1] = path2[1] = path3[1] = path4[1] = 00;
path1[2] = path2[2] = lookupTable[0,1];
path3[2] = path4[2] = lookupTable[0,2];
while(t<8){
    if(t===0){
        #get branch metric
        $hd=hammingDistance(currentState,nextState);
    }else{
        while(temp<statesNum){
            $hd=hammingDistance(currentState,nextState);
            #compare
        }
        temp++;
    }
t++;
}
function hammingDistance(a,b){
    return (a XOR b);
}