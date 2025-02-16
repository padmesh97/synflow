import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common'; 
import { HttpClient } from '@angular/common/http';
import { HttpClientModule } from '@angular/common/http';
import { environment } from '../environments/environment';
import * as AI_CONFIG from '../assets/ai-config.json'; 

interface Block {
  label: string;
  api: string;
  input: { type: string };
  output: { type: string };
}

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet, CommonModule, FormsModule, HttpClientModule],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
})
export class AppComponent {
  tasks = AI_CONFIG.tasks;
  availableBlocks: Block[] = [];
  selectedTask: string = '';
  taskSelected: boolean = false;
  userInput: string = '';
  inputType: string = 'TEXT'; // Default input type
  selectedFile: File | null = null; // Store selected file
  pipelineBlocks: Block[] = [];
  pipeLineResponseStatus: string = '';
  pipeLineResponse: string = '';

  constructor(private http: HttpClient) {
    this.availableBlocks = Object.values(AI_CONFIG.blocks) as Block[];
  }

  selectTask(taskId: string) {
    this.taskSelected = true;
    this.pipelineBlocks = [];
    this.pipeLineResponse = '';
    this.pipeLineResponseStatus = '';

    for (const task of this.tasks) {
      if (task.id !== null && taskId === task.id) {
        let blockCount: number = 1;
        for (const blkKey of task.template) {
          const block: Block | undefined = (AI_CONFIG.blocks as Record<string, Block>)[blkKey];

          if (block) {
            if (blockCount == 1) {
              this.inputType = block.input.type;
            }
            this.pipelineBlocks.push(block);
            blockCount++;
          }
        }
      }
    }
  }

  onFileSelected(event: any) {
    if (event.target.files.length > 0) {
      this.selectedFile = event.target.files[0];
    }
  }

  addBlock(block: Block) {
    this.pipelineBlocks.push(block);
    this.refreshBlocks(block);
  }

  removeBlock(block: Block) {
    this.pipelineBlocks = this.pipelineBlocks.filter(blk => blk.label !== block.label);
    this.refreshBlocks(block);
  }

  refreshBlocks(block: Block){
    if(this.pipelineBlocks.length > 0){
      this.inputType = this.pipelineBlocks[0].input.type;
      this.taskSelected = true;
    }
    else{
      this.taskSelected = false;
    }
  }

  async executePipeline() {
    if (!this.userInput && !this.selectedFile && this.pipelineBlocks.length > 0) {
        alert('Please supply correct input details before executing.');
        return;
    }

    if (this.pipelineBlocks.length == 0) { 
        alert('Please supply correct block details before executing.');
        return;
    }
    

    let currentInput = this.userInput;
    
    for (const block of this.pipelineBlocks) {
      try {
        let response: any;
        let url = environment.API_PATH + block.api;

        if (this.selectedFile && this.inputType === 'image') {
          const formData = new FormData();
          formData.append('file', this.selectedFile);
          response = await this.http.post(url, formData).toPromise();
        } else {
          response = await this.http.post(url, { input: currentInput }).toPromise();
        }

        if (response?.status === 'success') {
          currentInput = response.output;
          this.pipeLineResponseStatus = 'success';
        } else {
          this.pipeLineResponseStatus = 'error';
          console.log("exec1");
          this.pipeLineResponse = `Error processing block-[${block.label}] with ERROR: ${response.error}`;
          return;
        }
      } catch (error) {
        this.pipeLineResponseStatus = 'error';
        this.pipeLineResponse = `Error processing block-[${block.label}] with ERROR:  ${error}`;
        return;
      }
    }

    this.pipeLineResponse = currentInput;
  }
}