#include <stdio.h>
#include <limits.h>
#include <stdlib.h>

#define N 11

char *labs[N] = {
"AX502","AX505","AX506","AX508","AX510","AX512",
"AX504","AX507","AX509","AX511","AX516"
};

int graph[N][N] = {

{0,0,0,0,0,0,1,1,0,0,0},
{0,0,1,0,0,0,1,0,0,0,0},
{0,1,0,1,0,0,0,1,0,0,0},
{0,0,1,0,1,0,0,0,0,0,0},
{0,0,0,1,0,1,0,0,0,1,0},
{0,0,0,0,1,0,0,0,0,0,0},
{1,1,0,0,0,0,0,0,0,0,0},
{1,0,1,0,0,0,0,0,1,0,0},
{0,0,0,0,0,0,0,1,0,1,0},
{0,0,0,0,1,0,0,0,1,0,1},
{0,0,0,0,0,0,0,0,0,1,0}

};

char *direction[N][N] = {

{"","","","","","","Left","Right","","",""},
{"","","Straight","","","","Down","","","",""},
{"","Straight","","Straight","","","","Down","","",""},
{"","","Straight","","Straight","","","","","",""},
{"","","","Straight","","Straight","","","","Down",""},
{"","","","","Straight","","","","","",""},
{"Right","Up","","","","","","","","",""},
{"Left","","Up","","","","","","Straight","",""},
{"","","","","","","","Straight","","Straight",""},
{"","","","","Up","","","","Straight","","Straight"},
{"","","","","","","","","","Straight",""}

};

int minDistance(int dist[], int visited[])
{
int min = INT_MAX, index = -1;

for(int i=0;i<N;i++)
{
if(!visited[i] && dist[i] <= min)
{
min = dist[i];
index = i;
}
}

return index;
}

void printPath(int parent[], int j)
{
if(parent[j] == -1)
return;

printPath(parent,parent[j]);
printf(" -> %s",labs[j]);
}

void dijkstra(int src,int dest)
{

int dist[N];
int visited[N];
int parent[N];

for(int i=0;i<N;i++)
{
dist[i]=INT_MAX;
visited[i]=0;
parent[i]=-1;
}

dist[src]=0;

for(int i=0;i<N-1;i++)
{

int u=minDistance(dist,visited);

if(u==-1)
break;

visited[u]=1;

for(int v=0;v<N;v++)
{

if(!visited[v] && graph[u][v] &&
dist[u]!=INT_MAX &&
dist[u]+graph[u][v]<dist[v])
{

parent[v]=u;
dist[v]=dist[u]+graph[u][v];

}

}

}

if(dist[dest]==INT_MAX)
{
printf("No path available\n");
return;
}

printf("Shortest distance: %d steps\n",dist[dest]);

printf("\nPath:\n%s",labs[src]);
printPath(parent,dest);

printf("\n\nDirections:\n");

int path[50];
int count=0;

int temp=dest;

while(temp!=-1)
{
path[count++]=temp;
temp=parent[temp];
}

int step=1;

for(int i=count-1;i>0;i--)
{

int from=path[i];
int to=path[i-1];

printf("Step %d: From %s go %s to reach %s\n",
step++,
labs[from],
direction[from][to],
labs[to]);

}

}

int main(int argc,char *argv[])
{

if(argc!=3)
{
printf("Usage: program source destination\n");
return 0;
}

int src=atoi(argv[1]);
int dest=atoi(argv[2]);

dijkstra(src,dest);

return 0;
}