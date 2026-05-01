#include <stdio.h>
#include <limits.h>
#include <stdlib.h>

#define N 22

char *labNames[N] = {
"AX-501A","AX-501B","AX-502","AX-503A","AX-503B",
"AX-504","AX-505A","AX-505B","AX-506","AX-507",
"AX-508","AX-509","AX-510","AX-511","AX-512",
"AX-513A","AX-513B","AX-514A","AX-514B",
"AX-515A","AX-515B","AX-516B"
};

int graph[N][N] = {0};
char *direction[N][N] = {0};

// ---------- DIJKSTRA ----------

int minDistance(int dist[], int visited[])
{
    int min = INT_MAX, index = -1;

    for(int i=0;i<N;i++)
        if(!visited[i] && dist[i] <= min)
            min = dist[i], index = i;

    return index;
}

void printPath(int parent[], int j)
{
    if(parent[j] == -1) return;
    printPath(parent,parent[j]);
    printf(" -> %s", labNames[j]);
}

void dijkstra(int src,int dest)
{
    int dist[N], visited[N], parent[N];

    for(int i=0;i<N;i++)
        dist[i]=INT_MAX, visited[i]=0, parent[i]=-1;

    dist[src]=0;

    for(int i=0;i<N-1;i++)
    {
        int u=minDistance(dist,visited);
        if(u==-1) break;

        visited[u]=1;

        for(int v=0;v<N;v++)
        {
            if(!visited[v] && graph[u][v] &&
               dist[u]+1 < dist[v])
            {
                parent[v]=u;
                dist[v]=dist[u]+1;
            }
        }
    }

    if(dist[dest]==INT_MAX)
    {
        printf("No path available\n");
        return;
    }

    printf("Shortest distance: %d steps\n",dist[dest]);

    printf("\nPath:\n%s",labNames[src]);
    printPath(parent,dest);

    printf("\n\nDirections:\n");

    int path[50],count=0,temp=dest;

    while(temp!=-1)
        path[count++]=temp, temp=parent[temp];

    for(int i=count-1,step=1;i>0;i--)
    {
        int from=path[i], to=path[i-1];

        char *dir = direction[from][to];
        if(dir==NULL || dir[0]=='\0') dir="Move";

        printf("Step %d: From %s go %s to reach %s\n",
               step++, labNames[from], dir, labNames[to]);
    }
}

// ---------- MAIN ----------

int main(int argc, char *argv[])
{
    if(argc != 3)
    {
        printf("Usage: dijkstra <source_index> <destination_index>\n");
        return 0;
    }

    int src = atoi(argv[1]);
    int dest = atoi(argv[2]);

    if(src < 0 || src >= N || dest < 0 || dest >= N)
    {
        printf("Invalid input\n");
        return 0;
    }

    // -------- GRAPH --------

    graph[2][5]=graph[5][2]=1;
    graph[2][1]=graph[1][2]=1;

    graph[5][9]=graph[9][5]=1;

    graph[6][7]=graph[7][6]=1;
    graph[7][8]=graph[8][7]=1;

    graph[0][1]=graph[1][0]=1;

    graph[1][3]=graph[3][1]=1;

    graph[3][4]=graph[4][3]=1;
    graph[3][9]=graph[9][3]=1;

    graph[8][10]=graph[10][8]=1;
    graph[10][12]=graph[12][10]=1;
    graph[12][14]=graph[14][12]=1;

    graph[8][9]=graph[9][8]=1;
    graph[10][11]=graph[11][10]=1;
    graph[12][13]=graph[13][12]=1;

    graph[9][11]=graph[11][9]=1;
    graph[11][13]=graph[13][11]=1;

    graph[13][21]=graph[21][13]=1;

    graph[17][18]=graph[18][17]=1;
    graph[19][20]=graph[20][19]=1;

    graph[17][19]=graph[19][17]=1;
    graph[19][21]=graph[21][19]=1;

    graph[15][16]=graph[16][15]=1;
    graph[15][17]=graph[17][15]=1;

    // -------- DIRECTIONS --------

    direction[2][5]="Left";
    direction[2][1]="Straight";

    direction[5][2]="Right";
    direction[5][9]="Straight";

    direction[6][7]="Right";
    direction[7][6]="Left";
    direction[7][8]="Straight";
    direction[8][7]="Back";

    direction[0][1]="Right";
    direction[1][0]="Left";
    direction[1][2]="Back";
    direction[1][3]="Straight";

    direction[3][4]="Left";
    direction[4][3]="Right";

    direction[3][9]="Right";
    direction[9][3]="Left";

    direction[8][10]="Straight";
    direction[10][8]="Back";

    direction[10][12]="Straight";
    direction[12][10]="Back";

    direction[12][14]="Straight";
    direction[14][12]="Back";

    direction[8][9]="Down";
    direction[9][8]="Up";

    direction[10][11]="Down";
    direction[11][10]="Up";

    direction[12][13]="Down";
    direction[13][12]="Up";

    direction[9][11]="Straight";
    direction[11][9]="Back";

    direction[11][13]="Right";
    direction[13][11]="Left";

    direction[13][21]="Right";
    direction[21][13]="Left";

    direction[17][18]="Right";
    direction[18][17]="Left";

    direction[19][20]="Right";
    direction[20][19]="Left";

    direction[17][19]="Straight";
    direction[19][17]="Back";

    direction[19][21]="Straight";
    direction[21][19]="Back";

    direction[15][16]="Right";
    direction[16][15]="Left";

    direction[15][17]="Straight";
    direction[17][15]="Back";

    // RUN
    dijkstra(src,dest);

    return 0;
}